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
        $this->activateAddonIfAny($invoice);
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

    public function activateAddonIfAny(Invoice $invoice): void
    {
        if (!$invoice->service_addon_id || !$invoice->addon_id) {
            return;
        }

        $serviceAddon = $invoice->serviceAddons ?? \App\Models\ServiceAddon::find($invoice->service_addon_id);
        if (!$serviceAddon) {
            return;
        }

        $addon = $invoice->addon ?? \App\Models\Addon::find($invoice->addon_id);
        if (!$addon) {
            return;
        }

        $service = $invoice->service ?? $serviceAddon->service;
        if (!$service) {
            return;
        }

        $serviceAddon->update([
            'status' => 'active',
            'activated_at' => now(),
            'next_billing_at' => $addon->billing_interval === 'one_time' ? null : $this->calculateAddonNextBilling($addon),
        ]);

        if ($service->server_extension === 'pterodactyl' && ($service->server_properties['server_id'] ?? null)) {
            $this->applyAddonResources($service, $addon);
        }

        \App\Services\ActivityLogService::log('addon_activated', $service, 'Addon activated after payment: '.$addon->name);
    }

    private function calculateAddonNextBilling(\App\Models\Addon $addon): \Carbon\Carbon
    {
        $period = $addon->billing_period ?? 1;
        return match ($addon->billing_interval) {
            'month' => now()->addMonths($period),
            'quarter' => now()->addMonths($period * 3),
            'semi_annual' => now()->addMonths($period * 6),
            'year' => now()->addYears($period),
            default => now()->addMonth(),
        };
    }

    private function applyAddonResources(\App\Models\Service $service, \App\Models\Addon $addon): void
    {
        if (!$addon->extra_ram && !$addon->extra_disk && !$addon->extra_cpu && !$addon->extra_databases && !$addon->extra_allocations && !$addon->extra_backups) {
            return;
        }

        try {
            $serverId = $service->server_properties['server_id'] ?? null;
            if (!$serverId) {
                return;
            }

            $provisioning = new \App\Services\Servers\PterodactylServer;
            $result = $provisioning->getServerInfo($service);

            if (!$result['success']) {
                Log::error('Failed to get server info for addon upgrade', [
                    'service_id' => $service->id,
                    'error' => $result['error'],
                ]);
                return;
            }

            $upgradeResult = $provisioning->upgradeServer($service, [], [
                'ram' => ($result['memory'] ?? 0) + $addon->extra_ram,
                'disk' => ($result['disk'] ?? 0) + $addon->extra_disk,
                'cpu' => ($result['cpu'] ?? 0) + $addon->extra_cpu,
            ]);

            if (!$upgradeResult['success']) {
                Log::error('Failed to upgrade server resources for addon', [
                    'service_id' => $service->id,
                    'addon_id' => $addon->id,
                    'error' => $upgradeResult['error'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception applying addon resources', [
                'service_id' => $service->id,
                'addon_id' => $addon->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
