<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function processInvoicePayment(Invoice $invoice, string $gateway, string $transactionId): bool
    {
        if ($invoice->status === 'paid') {
            return true;
        }

        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        Transaction::create([
            'user_id' => $invoice->user_id,
            'invoice_id' => $invoice->id,
            'status' => 'completed',
            'amount' => $invoice->total,
            'currency_id' => $invoice->currency_id,
            'gateway' => $gateway,
            'gateway_id' => $transactionId,
            'completed_at' => now(),
        ]);

        $this->provisionInvoiceServices($invoice);
        $this->updateOrderStatus($invoice);

        Log::info("Invoice {$invoice->number} paid via {$gateway}");

        return true;
    }

    public function provisionInvoiceServices(Invoice $invoice): void
    {
        if ($invoice->service) {
            $services = collect([$invoice->service]);
        } else {
            // Find pending services linked to this invoice's order
            $orderId = $invoice->service?->order_id
                ?? $invoice->user->services()->where('status', 'pending')->first()?->order_id
                ?? 0;
            $services = $invoice->user->services()
                ->where('order_id', $orderId)
                ->where('status', 'pending')
                ->get();
        }

        $provisioning = new ServerProvisioningService;

        foreach ($services as $service) {
            if ($service->status === 'pending') {
                $result = $provisioning->provision($service);
                if ($result['success']) {
                    $billingService = new BillingService;
                    $service->update([
                        'status' => 'active',
                        'activated_at' => now(),
                        'next_billing_at' => $billingService->calculateNextBilling($service),
                    ]);
                } else {
                    Log::error('Service provisioning failed', [
                        'invoice_id' => $invoice->id,
                        'service_id' => $service->id,
                        'error' => $result['error'] ?? 'Unknown error',
                    ]);
                }
            }
        }
    }

    public function updateOrderStatus(Invoice $invoice): void
    {
        if ($invoice->service && $invoice->service->order) {
            $invoice->service->order->markPaidIfComplete();
        }
    }
}
