<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Service;
use App\Models\TaxRate;
use App\Notifications\InvoiceOverdue;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class BillingService
{
    public function generateInvoice(Service $service): ?Invoice
    {
        $pricing = $service->pricing;
        if (! $pricing) {
            return null;
        }

        $currency = $pricing->currencies->first();
        if (! $currency) {
            return null;
        }

        $amount = $currency->pivot->amount;
        $taxAmount = 0;
        $taxRate = null;

        // Apply tax based on user's location
        $user = $service->user;
        if ($user) {
            $taxRate = TaxRate::findByLocation($user->country, $user->state, $user->zip_code);
            if ($taxRate) {
                $taxAmount = $taxRate->calculateTax($amount);
            }
        }

        $total = $taxRate && $taxRate->is_inclusive ? $amount : $amount + $taxAmount;

        $invoice = Invoice::create([
            'user_id' => $service->user_id,
            'service_id' => $service->id,
            'status' => 'pending',
            'subtotal' => $amount,
            'tax' => $taxAmount,
            'total' => $total,
            'currency_id' => $currency->id,
            'tax_rate_id' => $taxRate?->id,
            'due_at' => now()->addDays(7),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => $service->product->name.' - '.$pricing->name,
            'amount' => $amount,
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

        return $invoice;
    }

    public function processOverdueInvoices(): void
    {
        $overdueInvoices = Invoice::whereIn('status', ['pending', 'overdue'])
            ->where('due_at', '<', now())
            ->whereHas('service', fn ($q) => $q->whereNotIn('status', ['cancelled', 'terminated']))
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = $invoice->due_at->diffInDays(now());

            // After 3 days: send reminder
            if ($daysOverdue >= 3 && $daysOverdue < 7) {
                $this->sendOverdueReminder($invoice);
            }

            // After 7 days: suspend service
            if ($daysOverdue >= 7 && $daysOverdue < 30 && $invoice->status !== 'overdue') {
                $this->suspendServiceForInvoice($invoice);
                $invoice->update(['status' => 'overdue']);
            }

            // After 30 days: terminate service
            if ($daysOverdue >= 30) {
                $this->terminateServiceForInvoice($invoice);
                $invoice->update(['status' => 'cancelled']);
            }
        }
    }

    private function sendOverdueReminder(Invoice $invoice): void
    {
        $user = $invoice->user;
        if ($user) {
            cache()->remember("invoice_reminder_{$invoice->id}", now()->addDays(1), function () use ($user, $invoice) {
                App::make(NotificationService::class)->notify($user, new InvoiceOverdue($invoice));
            });
        }
    }

    public function suspendServiceForInvoice(Invoice $invoice): void
    {
        $service = $invoice->service;
        if ($service && $service->status === 'active') {
            $provisioning = new ServerProvisioningService;
            $result = $provisioning->suspend($service);

            if ($result['success']) {
                Log::info("Service {$service->id} suspended for overdue invoice {$invoice->number}");
            }
        }
    }

    private function terminateServiceForInvoice(Invoice $invoice): void
    {
        $service = $invoice->service;
        if ($service && in_array($service->status, ['active', 'suspended'])) {
            $provisioning = new ServerProvisioningService;
            $result = $provisioning->terminate($service);

            if ($result['success']) {
                $service->update([
                    'status' => 'terminated',
                    'terminated_at' => now(),
                ]);
                Log::info("Service {$service->id} terminated for overdue invoice {$invoice->number}");
            }
        }
    }

    public function processRenewals(): void
    {
        $services = Service::where('status', 'active')
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', now()->addDays(1))
            ->get();

        $processedServiceIds = cache()->get('processed_renewals', []);

        foreach ($services as $service) {
            $cacheKey = "renewal_invoice_{$service->id}_" . $service->next_billing_at->format('Y-m-d');
            if (cache()->has($cacheKey)) {
                continue;
            }

            $invoice = $this->generateInvoice($service);

            if ($invoice) {
                $service->update([
                    'next_billing_at' => $this->calculateNextBilling($service),
                ]);

                cache()->put($cacheKey, true, now()->addDays(30));

                Log::info("Generated renewal invoice {$invoice->number} for service {$service->id}");
            }
        }
    }

    public function calculateNextBilling(Service $service): Carbon
    {
        $pricing = $service->pricing;
        if (! $pricing) {
            return now()->addMonth();
        }

        return match ($pricing->interval) {
            'day' => now()->addDays($pricing->billing_period),
            'week' => now()->addWeeks($pricing->billing_period),
            'month' => now()->addMonths($pricing->billing_period),
            'year' => now()->addYears($pricing->billing_period),
            default => now()->addMonth(),
        };
    }

    public function processPayment(Invoice $invoice, string $gateway, string $transactionId): bool
    {
        $paymentService = new PaymentService;

        return $paymentService->processInvoicePayment($invoice, $gateway, $transactionId);
    }

    public function generateOrderInvoice(Order $order): Invoice
    {
        $taxAmount = $order->tax ?? 0;
        $taxRate = null;
        $total = $order->total ?? $order->subtotal;

        $user = $order->user;
        if ($user) {
            $taxRate = TaxRate::findByLocation($user->country, $user->state, $user->zip_code);
        }

        $invoice = Invoice::create([
            'user_id' => $order->user_id,
            'status' => 'pending',
            'subtotal' => $order->subtotal,
            'tax' => $taxAmount,
            'total' => $total,
            'currency_id' => $order->currency_id,
            'tax_rate_id' => $taxRate?->id,
            'due_at' => now()->addDays(7),
        ]);

        foreach ($order->items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item->product->name.' - '.$item->pricing->name,
                'amount' => $item->price,
                'quantity' => $item->quantity,
            ]);
        }

        if ($taxRate && $taxAmount > 0) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "Tax ({$taxRate->name} - {$taxRate->rate}%)",
                'amount' => $taxAmount,
                'quantity' => 1,
            ]);
        }

        return $invoice;
    }
}
