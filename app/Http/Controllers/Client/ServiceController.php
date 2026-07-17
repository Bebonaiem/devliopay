<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Service;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\ServerProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $user->services()
            ->with(['product', 'pricing']);

        if ($request->filled('status') && in_array($request->status, ['active', 'pending', 'suspended', 'terminated', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        $services = $query->latest()->get();

        return view('client.services.index', compact('services'));
    }

    public function show(Service $service)
    {
        $this->authorize('view', $service);

        $service->load(['product', 'pricing', 'order', 'addons']);

        if ($service->server_extension === 'pterodactyl' && ($service->server_properties['ip_address'] ?? null) === null && ($service->server_properties['server_id'] ?? null)) {
            try {
                $provisioning = new ServerProvisioningService;
                $serverInfo = $provisioning->getServerInfo($service);
                if ($serverInfo['success']) {
                    $service->update([
                        'server_properties' => array_merge($service->server_properties ?? [], [
                            'ip_address' => $serverInfo['ip_address'] ?? null,
                            'port' => $serverInfo['port'] ?? null,
                        ]),
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to auto-fetch server IP/port', [
                    'service_id' => $service->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $panelUrl = null;
        if ($service->server_extension === 'pterodactyl' && ($service->server_properties['identifier'] ?? $service->server_properties['server_id'] ?? null)) {
            $panelUrl = rtrim(Setting::get('pterodactyl_host', ''), '/').'/server/'.($service->server_properties['identifier'] ?? $service->server_properties['server_id']);
        }

        $availableAddons = Addon::where('is_active', true)
            ->where(function ($query) use ($service) {
                $query->whereNull('server_extension')
                    ->orWhere('server_extension', $service->server_extension);
            })
            ->whereNotIn('id', $service->addons->pluck('id'))
            ->orderBy('sort_order')
            ->get();

        return view('client.services.show', compact('service', 'panelUrl', 'availableAddons'));
    }

    public function status(Service $service)
    {
        $this->authorize('view', $service);

        if ($service->server_extension !== 'pterodactyl') {
            return response()->json(['status' => 'unknown']);
        }

        $provisioning = new ServerProvisioningService;
        $result = $provisioning->getServerInfo($service);

        if ($result['success']) {
            return response()->json([
                'status' => $result['status'],
                'ip_address' => $result['ip_address'],
                'port' => $result['port'],
                'memory' => $result['memory'],
                'disk' => $result['disk'],
                'cpu' => $result['cpu'],
            ]);
        }

        return response()->json(['status' => 'unknown']);
    }

    public function cancel(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $request->validate([
            'cancel_type' => 'required|in:end_of_period,immediate',
        ]);

        if ($request->cancel_type === 'immediate') {
            if ($service->server_extension === 'pterodactyl' && ($service->server_properties['server_id'] ?? null)) {
                $provisioning = new ServerProvisioningService;
                $provisioning->terminate($service);
            }

            $service->update([
                'status' => 'cancelled',
                'terminated_at' => now(),
                'next_billing_at' => null,
            ]);

            \App\Models\Invoice::where('service_id', $service->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->update(['status' => 'cancelled']);

            ActivityLogService::log('service_cancelled', $service, 'Client cancelled service immediately');

            return redirect()->route('client.services.show', $service)
                ->with('success', 'Service cancelled and server terminated immediately');
        }

        $service->update([
            'status' => 'cancelled',
        ]);

        \App\Models\Invoice::where('service_id', $service->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->update(['status' => 'cancelled']);

        ActivityLogService::log('service_cancelled', $service, 'Client scheduled cancellation at end of billing period');

        return redirect()->route('client.services.show', $service)
            ->with('success', 'Service will be cancelled at the end of the billing period');
    }

    public function purchaseAddon(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $request->validate([
            'addon_id' => 'required|exists:addons,id',
        ]);

        $addon = \App\Models\Addon::findOrFail($request->addon_id);

        if (!$addon->is_active) {
            return back()->with('error', 'This addon is no longer available');
        }

        if ($service->addons()->where('addon_id', $addon->id)->where('status', 'active')->exists()) {
            return back()->with('error', 'You already have this addon');
        }

        $existingPending = $service->addons()->where('addon_id', $addon->id)->where('status', 'pending')->first();
        if ($existingPending) {
            $invoice = \App\Models\Invoice::where('service_addon_id', $existingPending->id)->where('status', 'pending')->first();
            if ($invoice) {
                return redirect()->route('client.invoices.show', $invoice)->with('info', 'You have a pending invoice for this addon');
            }
        }

        $currency = $service->pricing->currencies->first() ?? \App\Models\Currency::where('is_default', true)->first();

        $taxAmount = 0;
        $taxRate = null;
        $user = $service->user;
        if ($user) {
            $taxRate = \App\Models\TaxRate::findByLocation($user->country, $user->state, $user->zip_code);
            if ($taxRate) {
                $taxAmount = $taxRate->calculateTax($addon->price);
            }
        }

        $total = $taxRate && $taxRate->is_inclusive ? $addon->price : $addon->price + $taxAmount;

        $serviceAddonId = $service->addons()->attach($addon->id, [
            'price' => $addon->price,
            'status' => 'pending',
        ]);

        $serviceAddon = \App\Models\ServiceAddon::where('service_id', $service->id)->where('addon_id', $addon->id)->where('status', 'pending')->latest()->first();

        $invoice = \App\Models\Invoice::create([
            'user_id' => $service->user_id,
            'service_id' => $service->id,
            'addon_id' => $addon->id,
            'service_addon_id' => $serviceAddon->id,
            'status' => 'pending',
            'subtotal' => $addon->price,
            'tax' => $taxAmount,
            'total' => $total,
            'currency_id' => $currency?->id,
            'tax_rate_id' => $taxRate?->id,
            'due_at' => now()->addDays(7),
        ]);

        \App\Models\InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Addon: '.$addon->name.' - '.$service->product->name,
            'amount' => $addon->price,
            'quantity' => 1,
        ]);

        if ($taxRate && $taxAmount > 0) {
            \App\Models\InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => "Tax ({$taxRate->name} - {$taxRate->rate}%)",
                'amount' => $taxAmount,
                'quantity' => 1,
            ]);
        }

        app(\App\Services\NotificationService::class)->notify(
            $service->user,
            new \App\Notifications\InvoiceCreated($invoice)
        );

        ActivityLogService::log('addon_invoice_created', $service, 'Invoice created for addon: '.$addon->name.' - Total: '.$total);

        return redirect()->route('client.invoices.show', $invoice)
            ->with('success', 'Invoice created for "'.$addon->name.'". Please complete your payment.');
    }

}
