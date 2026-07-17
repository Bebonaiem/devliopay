<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CreditService;
use App\Services\Gateways\GatewayFactory;
use App\Services\Gateways\StripeGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CreditController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $creditService = new CreditService;
        $balance = $creditService->getBalance($user);
        $history = $creditService->getHistory($user);
        $availableGateways = GatewayFactory::available();

        return view('client.credits.index', compact('balance', 'history', 'availableGateways'));
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5|max:1000',
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();
        $paymentMethod = $request->payment_method;

        // For PayPal or redirect-based gateways
        if ($paymentMethod !== 'stripe') {
            $gateway = GatewayFactory::make($paymentMethod);
            if ($gateway && method_exists($gateway, 'createCreditDepositCheckoutUrl')) {
                $checkoutUrl = $gateway->createCreditDepositCheckoutUrl($user, $request->amount);
                if ($checkoutUrl) {
                    return redirect($checkoutUrl);
                }
            }

            $settingKey = $paymentMethod . '_enabled';
            $enabled = \App\Models\Setting::get($settingKey, false);
            $configured = $gateway ? true : false;

            if (! $enabled) {
                return back()->with('error', ucfirst($paymentMethod) . ' is not enabled. Please contact support.');
            }
            if (! $configured) {
                return back()->with('error', ucfirst($paymentMethod) . ' is not fully configured. Please contact support.');
            }

            return back()->with('error', 'Failed to create ' . ucfirst($paymentMethod) . ' checkout session. Please try again later.');
        }

        // For Stripe, redirect to credits page (embedded checkout handles it)
        session(['credit_deposit_amount' => $request->amount]);

        return redirect()->route('client.credits.index')->with('show_stripe_checkout', true);
    }

    public function createCheckoutSession(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:5|max:1000',
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();
        $paymentMethod = $request->payment_method;

        if ($paymentMethod === 'paypal') {
            $gateway = GatewayFactory::make('paypal');
            if (! $gateway || ! method_exists($gateway, 'createCreditDepositCheckoutUrl')) {
                return response()->json(['error' => 'PayPal gateway is not configured.'], 422);
            }

            $checkoutUrl = $gateway->createCreditDepositCheckoutUrl($user, $request->amount);
            if (! $checkoutUrl) {
                return response()->json(['error' => 'Failed to create PayPal checkout session.'], 500);
            }

            return response()->json(['redirectUrl' => $checkoutUrl]);
        }

        if ($paymentMethod !== 'stripe') {
            return response()->json(['error' => 'Only Stripe and PayPal are supported for embedded checkout.'], 422);
        }

        $gateway = GatewayFactory::make('stripe');
        if (! $gateway instanceof StripeGateway) {
            return response()->json(['error' => 'Stripe gateway is not configured.'], 422);
        }

        $result = $gateway->createCreditDepositEmbeddedCheckoutClientSecret($user, $request->amount);

        if (! $result['success']) {
            return response()->json(['error' => $result['error'] ?? 'Failed to create checkout session.'], 500);
        }

        session(['credit_deposit_amount' => $request->amount]);

        return response()->json([
            'clientSecret' => $result['client_secret'],
            'publishableKey' => $gateway->getPublishableKey(),
        ]);
    }

    public function depositSuccess(Request $request)
    {
        $gateway = $request->query('gateway', session('payment_gateway', 'stripe'));
        $sessionId = $request->query('token') ?: session('stripe_session_id') ?: $request->query('session_id');
        session()->forget('stripe_session_id');

        $user = Auth::user();
        $amount = session('credit_deposit_amount', 0);
        session()->forget('credit_deposit_amount');
        session()->forget('credit_deposit_order_id');

        $isPopup = $request->has('gateway');

        if ($amount > 0) {
            // For Stripe, verify session status using stored session ID
            if ($gateway === 'stripe' && $sessionId && $sessionId !== '{CHECKOUT_SESSION_ID}') {
                try {
                    $secretKey = Setting::get('stripe_restricted_key')
                        ?: Setting::get('stripe_secret_key')
                        ?: config('services.stripe.secret');

                    $http = Http::withHeaders(['Authorization' => 'Bearer '.$secretKey])
                        ->timeout(30);

                    if (app()->environment('local', 'testing')) {
                        $http = $http->withoutVerifying();
                    }

                    $response = $http->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

                    if ($response->successful()) {
                        $session = $response->json();
                        if ($session['status'] !== 'complete') {
                            return $this->popupOrRedirect($isPopup, 'client.credits.index', 'Payment was not completed. Please try again.', 'error');
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Stripe session verification failed for credit deposit', ['error' => $e->getMessage()]);
                }
            }

            // For PayPal, capture the order
            if ($gateway === 'paypal' && $sessionId) {
                $gatewayInstance = GatewayFactory::make('paypal');
                if ($gatewayInstance && method_exists($gatewayInstance, 'captureOrder')) {
                    $result = $gatewayInstance->captureOrder($sessionId);
                    if (! $result['success']) {
                        Log::error('PayPal capture failed in depositSuccess', ['order_id' => $sessionId, 'result' => $result]);
                        return $this->popupOrRedirect($isPopup, 'client.credits.index', 'Payment capture failed. Please try again.', 'error');
                    }
                }
            }

            $creditService = new CreditService;
            $creditService->deposit($user, $amount, 'Credit deposit via '.ucfirst($gateway));

            return $this->popupOrRedirect($isPopup, 'client.credits.index', \App\Models\Currency::defaultSymbol().number_format($amount, 2).' has been added to your account balance!', 'success');
        }

        return $this->popupOrRedirect($isPopup, 'client.credits.index', 'Invalid deposit amount.', 'error');
    }

    private function popupOrRedirect(bool $isPopup, string $route, string $message, string $type)
    {
        if ($isPopup) {
            $url = route($route);
            $bgColor = $type === 'success' ? 'emerald' : 'red';
            $icon = $type === 'success' ? 'check-circle' : 'alert-circle';

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

        return redirect()->route($route)->with($type, $message);
    }

    public function paypalCancel()
    {
        $url = route('client.credits.index');

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

    public function apply(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        $user = Auth::user();
        $creditService = new CreditService;

        if ($creditService->getBalance($user) < $request->amount) {
            return back()->with('error', 'Insufficient credit balance');
        }

        $invoice = $user->invoices()->where('id', $request->invoice_id)->firstOrFail();

        if ($creditService->withdraw($user, $request->amount, "Credit applied to invoice {$invoice->number}")) {
            $newTotal = max(0, $invoice->total - $request->amount);
            $credit = $invoice->credit + $request->amount;

            $invoice->update([
                'total' => $newTotal,
                'credit' => $credit,
            ]);

            if ($newTotal <= 0) {
                $invoice->update(['status' => 'paid', 'paid_at' => now()]);
            }

            return redirect()->route('client.invoices.show', $invoice)
                ->with('success', 'Credit applied successfully!');
        }

        return back()->with('error', 'Failed to apply credit');
    }
}
