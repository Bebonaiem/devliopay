<?php

namespace App\Services\Gateways;

use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CreditService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeGateway implements GatewayInterface
{
    private const API_VERSION = '2026-06-24.dahlia';

    private string $secretKey;

    private string $publishableKey;

    private string $webhookSecret;

    public function __construct()
    {
        // Prefer restricted key (RAK) over secret key — Stripe best practice
        $dbRak = Setting::get('stripe_restricted_key');
        $dbSecret = Setting::get('stripe_secret_key');
        $this->secretKey = (! empty($dbRak) && strlen($dbRak) > 5)
            ? $dbRak
            : ((! empty($dbSecret) && strlen($dbSecret) > 5) ? $dbSecret : (config('services.stripe.restricted_key') ?: config('services.stripe.secret') ?? ''));

        $this->publishableKey = Setting::get('stripe_publishable_key') ?: (config('services.stripe.publishable_key') ?? '');

        // Auto-generate webhook secret if not configured
        $dbWhSecret = Setting::get('stripe_webhook_secret');
        $this->webhookSecret = (! empty($dbWhSecret) && $dbWhSecret !== 'whsec_placeholder')
            ? $dbWhSecret
            : (config('services.stripe.webhook_secret') ?: '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->secretKey) && strlen($this->secretKey) > 5;
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    public static function generateWebhookSecret(): string
    {
        return 'whsec_'.bin2hex(random_bytes(32));
    }

    public function getName(): string
    {
        return 'stripe';
    }

    public function getDisplayName(): string
    {
        return 'Credit/Debit Card (Stripe)';
    }

    public function charge(Invoice $invoice, array $params = []): array
    {
        try {
            $response = $this->apiPost('https://api.stripe.com/v1/charges', [
                'amount' => (int) ($invoice->total * 100),
                'currency' => strtolower($invoice->currency?->code ?? 'usd'),
                'customer' => $params['customer_id'] ?? null,
                'source' => $params['source'] ?? null,
                'description' => 'Invoice '.$invoice->number,
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'transaction_id' => $data['id'],
                    'status' => $data['status'] === 'succeeded' ? 'completed' : 'pending',
                ];
            }

            Log::error('Stripe charge failed', ['response' => $response->json()]);

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Payment failed',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe charge exception', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createCheckoutUrl(Invoice $invoice, array $params = []): ?string
    {
        $result = $this->createEmbeddedCheckoutClientSecret($invoice, $params);

        return $result['client_secret'] ?? null;
    }

    public function createEmbeddedCheckoutClientSecret(Invoice $invoice, array $params = []): array
    {
        try {
            $response = $this->apiPost('https://api.stripe.com/v1/checkout/sessions', [
                'ui_mode' => 'elements',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower($invoice->currency?->code ?? 'usd'),
                            'product_data' => [
                                'name' => 'Invoice '.$invoice->number,
                                'description' => 'Payment for invoice '.$invoice->number,
                            ],
                            'unit_amount' => (int) ($invoice->total * 100),
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'return_url' => route('client.invoices.success', $invoice),
                'customer_email' => $invoice->user->email,
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $clientSecret = $data['client_secret'] ?? null;
                if ($clientSecret) {
                    $sessionId = $data['id'] ?? null;
                    session(['payment_gateway' => 'stripe', 'stripe_session_id' => $sessionId]);

                    return ['success' => true, 'client_secret' => $clientSecret];
                }
            }

            Log::error('Stripe embedded checkout session creation failed', [
                'status' => $response->status(),
                'response' => $response->json(),
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total,
            ]);

            return ['success' => false, 'error' => 'Failed to create checkout session'];
        } catch (\Exception $e) {
            Log::error('Stripe embedded checkout exception', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createCreditDepositEmbeddedCheckoutClientSecret(User $user, float $amount): array
    {
        try {
            $response = $this->apiPost('https://api.stripe.com/v1/checkout/sessions', [
                'ui_mode' => 'elements',
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower(config('app.currency', 'usd')),
                            'product_data' => [
                                'name' => 'Credit Deposit',
                                'description' => 'Add '.\App\Models\Currency::defaultSymbol().number_format($amount, 2).' to your account balance',
                            ],
                            'unit_amount' => (int) ($amount * 100),
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'return_url' => route('client.credits.deposit-success'),
                'customer_email' => $user->email,
                'metadata' => [
                    'user_id' => $user->id,
                    'type' => 'credit_deposit',
                    'amount' => $amount,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $clientSecret = $data['client_secret'] ?? null;
                if ($clientSecret) {
                    $sessionId = $data['id'] ?? null;
                    session(['payment_gateway' => 'stripe', 'stripe_session_id' => $sessionId]);

                    return ['success' => true, 'client_secret' => $clientSecret];
                }
            }

            Log::error('Stripe credit deposit checkout creation failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return ['success' => false, 'error' => 'Failed to create checkout session'];
        } catch (\Exception $e) {
            Log::error('Stripe credit deposit checkout exception', ['error' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createCreditDepositCheckoutUrl(User $user, float $amount): ?string
    {
        $result = $this->createCreditDepositEmbeddedCheckoutClientSecret($user, $amount);

        return $result['client_secret'] ?? null;
    }

    public function refund(string $transactionId, float $amount): array
    {
        try {
            $response = $this->apiPost('https://api.stripe.com/v1/refunds', [
                'payment_intent' => $transactionId,
                'amount' => (int) ($amount * 100),
            ]);

            if ($response->successful()) {
                return ['success' => true, 'refund_id' => $response->json()['id']];
            }

            return ['success' => false, 'error' => 'Refund failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getTransactionStatus(string $transactionId): string
    {
        try {
            $response = $this->apiGet("https://api.stripe.com/v1/charges/{$transactionId}");

            if ($response->successful()) {
                return $response->json()['status'] === 'succeeded' ? 'completed' : 'pending';
            }

            return 'unknown';
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    public function handleWebhook(array $payload): void
    {
        $type = $payload['type'] ?? '';
        $data = $payload['data']['object'] ?? [];

        Log::info('Stripe webhook received', ['type' => $type]);

        match ($type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($data),
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($data),
            'charge.refunded' => $this->handleChargeRefunded($data),
            default => null,
        };
    }

    public function verifyWebhookSignature(string $payload, string $sigHeader): bool
    {
        if (empty($this->webhookSecret) || empty($sigHeader)) {
            return false;
        }

        $elements = [];
        foreach (explode(',', $sigHeader) as $pair) {
            $parts = explode('=', $pair, 2);
            if (count($parts) === 2) {
                $elements[$parts[0]] = $parts[1];
            }
        }

        $timestamp = $elements['t'] ?? '';
        $signatures = isset($elements['v1']) ? explode(' ', $elements['v1']) : [];

        if (empty($timestamp) || empty($signatures)) {
            return false;
        }

        // Reject timestamps older than 5 minutes
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;
        $expectedSig = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        foreach ($signatures as $sig) {
            if (hash_equals($expectedSig, $sig)) {
                return true;
            }
        }

        return false;
    }

    private function handleCheckoutCompleted(array $data): void
    {
        $type = $data['metadata']['type'] ?? 'invoice';

        if ($type === 'credit_deposit') {
            $userId = $data['metadata']['user_id'] ?? null;
            $amount = (float) ($data['metadata']['amount'] ?? 0);

            if ($userId && $amount > 0) {
                $user = User::find($userId);
                if ($user) {
                    $creditService = new CreditService;
                    $creditService->deposit($user, $amount, 'Credit deposit via Stripe (webhook confirmed)');
                    Log::info("Credit deposit confirmed via webhook for user {$userId}: \${$amount}");
                }
            }

            return;
        }

        $invoiceId = $data['metadata']['invoice_id'] ?? null;
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                $paymentService = new PaymentService;
                $transactionId = $data['payment_intent'] ?? $data['id'] ?? null;
                $paymentService->processInvoicePayment($invoice, 'stripe', $transactionId);
            }
        }
    }

    private function handlePaymentSucceeded(array $data): void
    {
        // Checkout session handling covers this; no extra action needed
    }

    private function handleChargeRefunded(array $data): void
    {
        $transactionId = $data['id'] ?? null;
        if ($transactionId) {
            $transaction = Transaction::where('gateway_id', $transactionId)->first();
            if ($transaction) {
                $transaction->update(['status' => 'refunded']);
                if ($transaction->invoice) {
                    $transaction->invoice->update(['status' => 'refunded']);
                }
                Log::info("Transaction {$transactionId} refunded via Stripe webhook");
            }
        }
    }

    private function apiPost(string $url, array $data)
    {
        $http = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Stripe-Version' => self::API_VERSION,
        ])->asForm()->timeout(30);

        if (app()->environment('local', 'testing')) {
            $http = $http->withoutVerifying();
        }

        return $http->post($url, $data);
    }

    private function apiGet(string $url)
    {
        $http = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Stripe-Version' => self::API_VERSION,
        ])->timeout(30);

        if (app()->environment('local', 'testing')) {
            $http = $http->withoutVerifying();
        }

        return $http->get($url);
    }
}
