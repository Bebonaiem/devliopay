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

class PayPalGateway implements GatewayInterface
{
    private string $clientId;

    private string $clientSecret;

    private string $mode;

    public function __construct()
    {
        $dbClientId = Setting::get('paypal_client_id');
        $dbClientSecret = Setting::get('paypal_client_secret');
        $this->clientId = (! empty($dbClientId)) ? $dbClientId : (config('services.paypal.client_id') ?? '');
        $this->clientSecret = (! empty($dbClientSecret)) ? $dbClientSecret : (config('services.paypal.client_secret') ?? '');
        $this->mode = Setting::get('paypal_mode') ?: (config('services.paypal.mode', 'sandbox') ?? 'sandbox');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret);
    }

    public function getName(): string
    {
        return 'paypal';
    }

    public function getDisplayName(): string
    {
        return 'PayPal';
    }

    private function getBaseUrl(): string
    {
        return $this->mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function apiPost(string $url, array $data, array $headers = [])
    {
        $http = Http::withHeaders(array_merge([
            'Content-Type' => 'application/json',
        ], $headers))->timeout(30);

        if (app()->environment('local', 'testing')) {
            $http = $http->withoutVerifying();
        }

        return $http->post($url, $data);
    }

    private function apiGet(string $url, array $headers = [])
    {
        $http = Http::withHeaders($headers)->timeout(30);

        if (app()->environment('local', 'testing')) {
            $http = $http->withoutVerifying();
        }

        return $http->get($url);
    }

    private function getAccessToken(): ?string
    {
        try {
            $http = Http::withHeaders([
                'Accept' => 'application/json',
                'Accept-Language' => 'en_US',
            ])->withBasicAuth($this->clientId, $this->clientSecret)
                ->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->asForm()->post($this->getBaseUrl().'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            Log::error('PayPal auth failed', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal auth exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function charge(Invoice $invoice, array $params = []): array
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return ['success' => false, 'error' => 'Failed to authenticate with PayPal'];
        }

        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($this->getBaseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => $invoice->number,
                        'amount' => [
                            'currency_code' => strtoupper($invoice->currency?->code ?? 'USD'),
                            'value' => number_format($invoice->total, 2, '.', ''),
                        ],
                        'description' => 'Invoice '.$invoice->number,
                    ],
                ],
                'application_context' => [
                    'return_url' => route('client.invoices.success', $invoice).'?payment=success&gateway=paypal',
                    'cancel_url' => route('client.invoices.paypal-cancel', $invoice),
                    'brand_name' => \App\Models\Setting::get('company_name', config('app.name', 'DevlioPay')),
                    'landing_page' => 'BILLING',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $approveUrl = collect($data['links'] ?? [])->firstWhere('rel', 'approve')['href'];

                return [
                    'success' => true,
                    'order_id' => $data['id'],
                    'approve_url' => $approveUrl,
                ];
            }

            Log::error('PayPal order creation failed', ['response' => $response->json()]);

            return ['success' => false, 'error' => 'Failed to create PayPal order'];
        } catch (\Exception $e) {
            Log::error('PayPal charge exception', ['error' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createCheckoutUrl(Invoice $invoice, array $params = []): ?string
    {
        $result = $this->charge($invoice, $params);
        if ($result['success']) {
            session(['payment_gateway' => 'paypal']);

            return $result['approve_url'];
        }

        return null;
    }

    public function createCreditDepositCheckoutUrl(User $user, float $amount): ?string
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return null;
        }

        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($this->getBaseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => 'credit_'.$user->id.'_'.time(),
                        'amount' => [
                            'currency_code' => strtoupper(config('app.currency', 'USD')),
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                        'description' => 'Account Credit Deposit - Add '.\App\Models\Setting::get('default_currency_symbol', '$').number_format($amount, 2).' credit',
                    ],
                ],
                'application_context' => [
                    'return_url' => route('client.credits.deposit-success').'?gateway=paypal',
                    'cancel_url' => route('client.credits.paypal-cancel'),
                    'brand_name' => \App\Models\Setting::get('company_name', config('app.name', 'DevlioPay')),
                    'landing_page' => 'BILLING',
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $approveUrl = collect($data['links'] ?? [])->firstWhere('rel', 'approve')['href'];

                if ($approveUrl) {
                    session([
                        'credit_deposit_amount' => $amount,
                        'credit_deposit_order_id' => $data['id'],
                    ]);

                    return $approveUrl;
                }
            }

            Log::error('PayPal credit deposit order failed', ['response' => $response->json()]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal credit deposit exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function captureOrder(string $orderId): array
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return ['success' => false, 'error' => 'Failed to authenticate'];
        }

        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
            ])->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $getResponse = $http->get($this->getBaseUrl()."/v2/checkout/orders/{$orderId}");

            if ($getResponse->successful()) {
                $orderData = $getResponse->json();
                $status = $orderData['status'] ?? '';

                if ($status === 'COMPLETED') {
                    $transactionId = $orderData['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

                    return [
                        'success' => true,
                        'transaction_id' => $transactionId,
                        'status' => 'COMPLETED',
                    ];
                }

                if ($status === 'APPROVED') {
                    $captureResponse = $http->withBody('{}', 'application/json')
                        ->post($this->getBaseUrl()."/v2/checkout/orders/{$orderId}/capture");

                    if ($captureResponse->successful()) {
                        $data = $captureResponse->json();

                        return [
                            'success' => true,
                            'transaction_id' => $data['purchase_units'][0]['payments']['captures'][0]['id'] ?? null,
                            'status' => $data['status'] ?? 'UNKNOWN',
                        ];
                    }

                    Log::error('PayPal capture API failed', [
                        'order_id' => $orderId,
                        'status' => $captureResponse->status(),
                        'response' => $captureResponse->json(),
                    ]);

                    $captureError = $captureResponse->json()['details'][0]['issue'] ?? '';

                    if ($captureError === 'COMPLIANCE_VIOLATION') {
                        Log::warning('PayPal COMPLIANCE_VIOLATION on capture - treating as approved payment (auto sweep pending)', ['order_id' => $orderId]);

                        return [
                            'success' => true,
                            'transaction_id' => $orderId,
                            'status' => 'APPROVED',
                        ];
                    }

                    return ['success' => false, 'error' => $captureResponse->json()['details'][0]['description'] ?? 'Failed to capture order'];
                }

                Log::error('PayPal order in unexpected state', ['order_id' => $orderId, 'status' => $status]);

                return ['success' => false, 'error' => 'Order is in '.$status.' state. Please try again.'];
            }

            Log::error('PayPal order fetch failed', ['order_id' => $orderId, 'status' => $getResponse->status(), 'response' => $getResponse->json()]);

            return ['success' => false, 'error' => 'Failed to fetch order details'];
        } catch (\Exception $e) {
            Log::error('PayPal capture exception', ['order_id' => $orderId, 'error' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refund(string $transactionId, float $amount, string $currencyCode = 'USD'): array
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return ['success' => false, 'error' => 'Failed to authenticate'];
        }

        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ])->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($this->getBaseUrl()."/v2/payments/captures/{$transactionId}/refund", [
                'amount' => [
                    'currency_code' => $currencyCode,
                    'value' => number_format($amount, 2, '.', ''),
                ],
            ]);

            return $response->successful()
                ? ['success' => true, 'refund_id' => $response->json()['id']]
                : ['success' => false, 'error' => 'Refund failed'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getTransactionStatus(string $transactionId): string
    {
        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return 'unknown';
        }

        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
            ])->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get($this->getBaseUrl()."/v2/payments/captures/{$transactionId}");

            if ($response->successful()) {
                return $response->json()['status'] === 'COMPLETED' ? 'completed' : 'pending';
            }

            return 'unknown';
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    public function handleWebhook(array $payload): void
    {
        $eventType = $payload['event_type'] ?? '';
        $resource = $payload['resource'] ?? [];

        Log::info('PayPal webhook received', ['type' => $eventType]);

        match ($eventType) {
            'PAYMENT.CAPTURE.COMPLETED' => $this->handleCaptureCompleted($resource, $payload),
            'PAYMENT.CAPTURE.REFUNDED' => $this->handleCaptureRefunded($resource),
            default => null,
        };
    }

    public function verifyWebhookSignature($request): bool
    {
        $webhookId = Setting::get('paypal_webhook_id') ?? config('services.paypal.webhook_id', '');
        if (empty($webhookId)) {
            Log::warning('PayPal webhook ID not configured - skipping signature verification');

            return true;
        }

        $headers = $request->headers;
        $transmissionId = $headers->get('PayPal-Transmission-Id', '');
        $timestamp = $headers->get('PayPal-Transmission-Time', '');
        $webhookEventId = $headers->get('PayPal-Webhook-Id', '');
        $certUrl = $headers->get('PayPal-Cert-Url', '');
        $actualSig = $headers->get('PayPal-Transmission-Sig', '');

        if (empty($transmissionId) || empty($timestamp) || empty($actualSig)) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return false;
        }

        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ])->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($this->getBaseUrl().'/v1/notifications/verify-webhook-signature', [
                'transmission_id' => $transmissionId,
                'transmission_time' => $timestamp,
                'cert_url' => $certUrl,
                'actual_event' => json_encode($request->all()),
                'webhook_id' => $webhookId,
                'expected_sig' => $actualSig,
            ]);

            if ($response->successful()) {
                $result = $response->json('verification_status');

                return $result === 'SUCCESS';
            }
        } catch (\Exception $e) {
            Log::error('PayPal webhook signature verification exception', ['error' => $e->getMessage()]);
        }

        return false;
    }

    private function handleCaptureCompleted(array $resource, array $payload = []): void
    {
        // Check for credit deposits first (custom_id starts with "credit_")
        $customId = $resource['custom_id'] ?? '';
        if (str_starts_with($customId, 'credit_')) {
            $parts = explode('_', $customId);
            $userId = $parts[1] ?? null;
            $amount = (float) ($resource['amount']['value'] ?? 0);

            if ($userId && $amount > 0) {
                $user = User::find($userId);
                if ($user) {
                    $creditService = new CreditService;
                    $creditService->deposit($user, $amount, 'Credit deposit via PayPal (webhook confirmed)');
                    Log::info("Credit deposit confirmed via PayPal webhook for user {$userId}: \${$amount}");
                }
            }

            return;
        }

        // Try to find invoice by custom_id
        $invoice = null;
        if ($customId) {
            $invoice = Invoice::where('number', $customId)->first();
        }

        // Fallback: check purchase_units for invoice_id in description
        if (! $invoice) {
            $description = $resource['description'] ?? '';
            if (preg_match('/Invoice\s+(INV-\w+)/', $description, $matches)) {
                $invoice = Invoice::where('number', $matches[1])->first();
            }
        }

        if ($invoice) {
            $paymentService = new PaymentService;
            $transactionId = $resource['id'] ?? null;
            $paymentService->processInvoicePayment($invoice, 'paypal', $transactionId);
        }
    }

    private function handleCaptureRefunded(array $resource): void
    {
        $transactionId = $resource['id'] ?? null;
        if ($transactionId) {
            $transaction = Transaction::where('gateway_id', $transactionId)->first();
            if ($transaction) {
                $transaction->update(['status' => 'refunded']);
                if ($transaction->invoice) {
                    $transaction->invoice->update(['status' => 'refunded']);
                }
                Log::info("Transaction {$transactionId} refunded via PayPal webhook");
            }
        }
    }
}
