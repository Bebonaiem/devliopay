<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Setting;
use App\Notifications\PaymentReceived;
use App\Services\Gateways\GatewayFactory;
use App\Services\Gateways\StripeGateway;
use App\Services\InvoicePdfService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->invoices()
            ->with(['items', 'currency']);

        if ($request->filled('status') && in_array($request->status, ['paid', 'pending', 'overdue'])) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->get();

        return view('client.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['items', 'transactions', 'currency', 'user', 'service']);
        $availableGateways = GatewayFactory::available();

        return view('client.invoices.show', compact('invoice', 'availableGateways'));
    }

    public function pdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $pdfService = new InvoicePdfService;

        return $pdfService->download($invoice);
    }

    public function createCheckoutSession(Invoice $invoice, Request $request): JsonResponse
    {
        $this->authorize('update', $invoice);

        $paymentMethod = $request->input('payment_method', 'stripe');
        $paymentService = new PaymentService;

        // Handle balance payment
        if ($paymentMethod === 'balance') {
            $user = Auth::user();
            if ($user->balance < $invoice->total) {
                return response()->json(['error' => 'Insufficient credit balance.'], 422);
            }

            $user->decrement('balance', $invoice->total);

            $paymentService->processInvoicePayment($invoice, 'balance', 'BAL-'.Str::random(12));
            app(NotificationService::class)->notify($user, new PaymentReceived($invoice, $invoice->total));

            return response()->json(['success' => true, 'paid' => true]);
        }

        // Handle gateway payment
        $gateway = GatewayFactory::make($paymentMethod);
        if (! $gateway) {
            return response()->json(['error' => 'Payment gateway is not configured.'], 422);
        }

        // Stripe embedded checkout
        if ($paymentMethod === 'stripe' && $gateway instanceof StripeGateway) {
            $result = $gateway->createEmbeddedCheckoutClientSecret($invoice, [
                'user' => Auth::user(),
            ]);

            if (! $result['success']) {
                return response()->json(['error' => $result['error'] ?? 'Failed to create payment session.'], 500);
            }

            return response()->json([
                'clientSecret' => $result['client_secret'],
                'publishableKey' => $gateway->getPublishableKey(),
            ]);
        }

        // Other gateways (PayPal redirect)
        $checkoutUrl = $gateway->createCheckoutUrl($invoice, [
            'user' => Auth::user(),
        ]);

        if (! $checkoutUrl) {
            return response()->json(['error' => 'Failed to create payment session.'], 500);
        }

        return response()->json(['redirectUrl' => $checkoutUrl]);
    }

    public function sessionStatus(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return response()->json(['error' => 'Missing session_id'], 400);
        }

        try {
            $secretKey = Setting::get('stripe_restricted_key')
                ?: Setting::get('stripe_secret_key')
                ?: config('services.stripe.secret');

            $http = Http::withHeaders([
                'Authorization' => 'Bearer '.$secretKey,
                'Stripe-Version' => '2026-06-24.dahlia',
            ])->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

            if ($response->successful()) {
                $session = $response->json();

                return response()->json([
                    'status' => $session['status'],
                    'customer_email' => $session['customer_details']['email'] ?? null,
                ]);
            }

            return response()->json(['error' => 'Session not found'], 404);
        } catch (\Exception $e) {
            Log::error('Stripe session status check failed', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Failed to verify session'], 500);
        }
    }

    public function pay(Invoice $invoice, Request $request)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $paymentMethod = $request->payment_method;
        $user = Auth::user();
        $paymentService = new PaymentService;

        // Handle balance payment
        if ($paymentMethod === 'balance') {
            if ($user->balance < $invoice->total) {
                return redirect()->route('client.invoices.show', $invoice)
                    ->with('error', 'Insufficient credit balance. Please add funds or choose another payment method.');
            }

            $user->decrement('balance', $invoice->total);

            $paymentService->processInvoicePayment($invoice, 'balance', 'BAL-'.Str::random(12));
            app(NotificationService::class)->notify($user, new PaymentReceived($invoice, $invoice->total));

            return redirect()->route('client.invoices.show', $invoice)
                ->with('success', 'Payment completed using credit balance!');
        }

        // Handle gateway payment
        $gateway = GatewayFactory::make($paymentMethod);
        if (! $gateway) {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('error', 'Payment gateway is not configured. Please contact support.');
        }

        // Stripe - redirect to invoice page (embedded checkout handles payment)
        if ($paymentMethod === 'stripe') {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('show_stripe_checkout', true);
        }

        // PayPal or other redirect gateways
        $checkoutUrl = $gateway->createCheckoutUrl($invoice, [
            'user' => $user,
        ]);

        if (! $checkoutUrl) {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('error', 'Failed to create payment session. Please try again.');
        }

        return redirect($checkoutUrl);
    }

    public function success(Invoice $invoice, Request $request)
    {
        $this->authorize('view', $invoice);

        $paymentService = new PaymentService;
        $isPopup = $request->has('gateway');

        // Handle PayPal return (query param based)
        if ($request->query('payment') === 'success') {
            $gateway = $request->query('gateway', session('payment_gateway', 'stripe'));

            if ($gateway === 'paypal') {
                $orderId = $request->query('token');
                if ($orderId) {
                    $gatewayInstance = GatewayFactory::make('paypal');
                    if ($gatewayInstance && method_exists($gatewayInstance, 'captureOrder')) {
                        $result = $gatewayInstance->captureOrder($orderId);
                        if ($result['success']) {
                            if ($invoice->status !== 'paid') {
                                $paymentService->processInvoicePayment($invoice, 'paypal', $result['transaction_id']);
                                app(NotificationService::class)->notify(Auth::user(), new PaymentReceived($invoice, $invoice->total));
                            }

                            return $this->paypalPopupOrRedirect($isPopup, $invoice, 'Payment processed successfully!', 'success');
                        }
                    }
                }
            }

            return $this->paypalPopupOrRedirect($isPopup, $invoice, 'Payment has been confirmed!', 'success');
        }

        // Handle Stripe return (session-stored ID or query param)
        $sessionId = session('stripe_session_id') ?: $request->query('session_id');
        session()->forget('stripe_session_id');

        if (! $sessionId || $sessionId === '{CHECKOUT_SESSION_ID}') {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('error', 'Payment session not found. Please try again.');
        }

        $paymentVerified = false;

        try {
            $secretKey = Setting::get('stripe_restricted_key')
                ?: Setting::get('stripe_secret_key')
                ?: config('services.stripe.secret');

            $http = Http::withHeaders([
                'Authorization' => 'Bearer '.$secretKey,
                'Stripe-Version' => '2026-06-24.dahlia',
            ])->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

            if ($response->successful()) {
                $session = $response->json();
                if ($session['status'] === 'complete') {
                    $paymentVerified = true;
                }
            }
        } catch (\Exception $e) {
            Log::error('Stripe session retrieval failed', ['error' => $e->getMessage()]);
        }

        if (! $paymentVerified) {
            return redirect()->route('client.invoices.show', $invoice)
                ->with('error', 'Payment could not be verified. Please try again or contact support.');
        }

        if ($invoice->status !== 'paid') {
            $paymentService->processInvoicePayment($invoice, 'stripe', $sessionId);
            app(NotificationService::class)->notify(Auth::user(), new PaymentReceived($invoice, $invoice->total));
        }

        // Provisioning is handled inside processInvoicePayment - no need to duplicate
        return redirect()->route('client.invoices.show', $invoice)
            ->with('success', 'Payment confirmed successfully!');
    }

    public function provision(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load('service');

        if (! $invoice->service) {
            return back()->with('error', 'No service linked to this invoice.');
        }

        if ($invoice->service->status !== 'pending') {
            return back()->with('error', 'Service is not in pending status.');
        }

        if ($invoice->status !== 'paid') {
            return back()->with('error', 'Invoice must be paid before provisioning.');
        }

        $provisioning = new ServerProvisioningService;
        $result = $provisioning->provision($invoice->service);

        if ($result['success']) {
            $invoice->service->update(['status' => 'active', 'activated_at' => now()]);

            return back()->with('success', 'Service provisioned successfully!');
        }

        Log::error('Manual provisioning failed', [
            'invoice_id' => $invoice->id,
            'service_id' => $invoice->service->id,
            'error' => $result['error'] ?? 'Unknown error',
        ]);

        return back()->with('error', 'Provisioning failed: '.($result['error'] ?? 'Unknown error').'. Please contact support.');
    }

    private function paypalPopupOrRedirect(bool $isPopup, Invoice $invoice, string $message, string $type)
    {
        if ($isPopup) {
            $url = route('client.invoices.show', $invoice);

            return new Response(
                '<!DOCTYPE html><html><head><title>Payment</title></head><body style="margin:0;display:flex;align-items:center;justify-content:center;height:100vh;background:#0f111a;font-family:system-ui;color:#e2e8f0;">'
                .'<div style="text-align:center;padding:2rem;"><div style="width:64px;height:64px;border-radius:50%;background:rgba('.($type==='success'?'16,185,129':'239,68,68').',0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;"><svg width="32" height="32" fill="none" stroke="'.($type==='success'?'#10b981':'#ef4444').'" stroke-width="2" viewBox="0 0 24 24"><path d="'.($type==='success'?'M20 6L9 17l-5-5':'M18 6L6 18M6 6l12 12').'"/></svg></div>'
                .'<p style="font-size:1rem;font-weight:600;margin-bottom:1.5rem;">'.e($message).'</p>'
                .'<button onclick="if(window.opener){window.opener.location.reload();window.close();}else{window.location=\''.$url.'\';}" style="padding:.75rem 2rem;border-radius:.75rem;background:#6366f1;color:white;border:none;font-weight:600;cursor:pointer;">Continue</button>'
                .'</div></body></html>',
                200,
                ['Content-Type' => 'text/html']
            );
        }

        return redirect()->route('client.invoices.show', $invoice)->with($type, $message);
    }

    public function paypalCancel(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $url = route('client.invoices.show', $invoice);

        return new Response(
            '<!DOCTYPE html><html><head><title>Payment Cancelled</title></head><body style="margin:0;display:flex;align-items:center;justify-content:center;height:100vh;background:#0f111a;font-family:system-ui;color:#e2e8f0;">'
            .'<div style="text-align:center;padding:2rem;"><div style="width:64px;height:64px;border-radius:50%;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;"><svg width="32" height="32" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg></div>'
            .'<p style="font-size:1rem;font-weight:600;margin-bottom:1.5rem;">Payment was cancelled.</p>'
            .'<button onclick="if(window.opener){window.opener.location.reload();window.close();}else{window.location=\''.$url.'\';}" style="padding:.75rem 2rem;border-radius:.75rem;background:#6366f1;color:white;border:none;font-weight:600;cursor:pointer;">Continue</button>'
            .'</div></body></html>',
            200,
            ['Content-Type' => 'text/html']
        );
    }
}
