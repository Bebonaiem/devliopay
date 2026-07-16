<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductPricing;
use App\Models\Service;
use App\Models\ServiceUpgrade;
use App\Models\TaxRate;
use App\Services\BillingService;
use App\Services\CreditService;
use App\Services\Gateways\GatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpgradeController extends Controller
{
    public function index(Service $service)
    {
        $this->authorize('view', $service);

        $service->load(['product.category', 'pricing']);

        $currentPricing = $service->pricing;
        $categoryId = $service->product->category_id;

        $availablePricings = ProductPricing::whereHas('product', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
                ->where('is_active', true);
        })
            ->where('id', '!=', $service->pricing_id)
            ->with(['product', 'currencies'])
            ->get();

        $currency = Currency::where('is_default', true)->first();

        $recentUpgrades = ServiceUpgrade::where('service_id', $service->id)
            ->with(['fromPricing', 'toPricing'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('client.upgrades.index', compact(
            'service', 'currentPricing', 'availablePricings', 'currency', 'recentUpgrades'
        ));
    }

    public function store(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $request->validate([
            'pricing_id' => 'required|exists:product_pricing,id',
        ]);

        $newPricing = ProductPricing::with('currencies')->find($request->pricing_id);
        $currentPricing = $service->pricing;

        $currency = Currency::where('is_default', true)->first();

        $currentPrice = $currentPricing->currencies->where('currency_id', $currency->id)->first()?->pivot?->amount ?? 0;
        $newPrice = $newPricing->currencies->where('currency_id', $currency->id)->first()?->pivot?->amount ?? 0;

        $priceDifference = $newPrice - $currentPrice;
        $type = $priceDifference >= 0 ? 'upgrade' : 'downgrade';

        $creditService = new CreditService;
        $user = Auth::user();
        $creditApplied = 0;
        $amountDue = $priceDifference;

        if ($priceDifference < 0) {
            $creditAmount = abs($priceDifference);
            $creditService->deposit($user, $creditAmount, 'Credit for downgrade from '.($currentPricing->name ?? '').' to '.($newPricing->name ?? ''));
            $amountDue = 0;
            $creditApplied = $creditAmount;
        }

        $upgrade = ServiceUpgrade::create([
            'service_id' => $service->id,
            'user_id' => Auth::id(),
            'from_pricing_id' => $currentPricing->id,
            'to_pricing_id' => $newPricing->id,
            'price_difference' => $priceDifference,
            'credit_applied' => $creditApplied,
            'amount_due' => max(0, $amountDue),
            'status' => $amountDue <= 0 ? 'completed' : 'pending',
            'type' => $type,
        ]);

        if ($amountDue <= 0) {
            $this->applyUpgrade($upgrade);
        }

        if ($upgrade->status === 'completed') {
            return redirect()->route('client.services.show', $service)
                ->with('success', ucfirst($type).' completed successfully!');
        }

        return redirect()->route('client.upgrades.pay', [$service, $upgrade])
            ->with('info', 'Please complete payment for the '.$type);
    }

    public function pay(Service $service, ServiceUpgrade $upgrade)
    {
        $this->authorize('view', $service);

        if ($upgrade->status !== 'pending') {
            return redirect()->route('client.services.show', $service);
        }

        return view('client.upgrades.pay', compact('service', 'upgrade'));
    }

    public function processPayment(Request $request, Service $service, ServiceUpgrade $upgrade)
    {
        $this->authorize('update', $service);

        if ($upgrade->status !== 'pending') {
            return back()->with('error', 'This upgrade has already been processed');
        }

        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $paymentMethod = $request->payment_method;

        // Handle balance payment
        if ($paymentMethod === 'balance') {
            $user = Auth::user();
            if ($user->balance < $upgrade->amount_due) {
                return back()->with('error', 'Insufficient credit balance.');
            }

            $creditService = new CreditService;
            $creditService->withdraw($user, $upgrade->amount_due, "Payment for upgrade on service #{$service->id}");
        } else {
            // For gateway payments, redirect to gateway
            $gateway = GatewayFactory::make($paymentMethod);
            if (! $gateway) {
                return back()->with('error', 'Payment gateway is not configured.');
            }

            // Create a temporary invoice for the upgrade
            $user = Auth::user();
            $taxRate = TaxRate::findByLocation($user->country, $user->state, $user->zip_code);
            $taxAmount = 0;
            if ($taxRate && ! $taxRate->is_inclusive) {
                $taxAmount = $taxRate->calculateTax($upgrade->amount_due);
            }

            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'service_id' => $service->id,
                'status' => 'pending',
                'subtotal' => $upgrade->amount_due,
                'tax' => $taxAmount,
                'total' => $upgrade->amount_due + $taxAmount,
                'currency_id' => Currency::where('is_default', true)->first()?->id,
                'tax_rate_id' => $taxRate?->id,
                'due_at' => now()->addHours(1),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "Upgrade from {$service->pricing->name} to {$upgrade->toPricing->name}",
                'amount' => $upgrade->amount_due,
                'quantity' => 1,
            ]);

            if ($taxRate && $taxAmount > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => "Tax ({$taxRate->name} - {$taxRate->rate}%)",
                    'amount' => $taxAmount,
                    'quantity' => 1,
                ]);
            }

            $checkoutUrl = $gateway->createCheckoutUrl($invoice, ['user' => Auth::user()]);
            if ($checkoutUrl) {
                session(['upgrade_pending_id' => $upgrade->id, 'upgrade_invoice_id' => $invoice->id]);

                return redirect($checkoutUrl);
            }

            return back()->with('error', 'Failed to create payment session.');
        }

        $upgrade->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        $this->applyUpgrade($upgrade);

        return redirect()->route('client.services.show', $service)
            ->with('success', 'Payment processed and upgrade completed!');
    }

    private function applyUpgrade(ServiceUpgrade $upgrade): void
    {
        $service = $upgrade->service;
        $newPricing = $upgrade->toPricing;

        $billingService = new BillingService;
        $service->update([
            'pricing_id' => $newPricing->id,
            'next_billing_at' => $billingService->calculateNextBilling($service),
        ]);
    }
}
