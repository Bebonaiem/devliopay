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

        if ($service->addons()->where('addon_id', $addon->id)->exists()) {
            return back()->with('error', 'You already have this addon');
        }

        $service->addons()->attach($addon->id, [
            'price' => $addon->price,
            'status' => 'active',
            'activated_at' => now(),
            'next_billing_at' => $addon->billing_period ? now()->addUnit($addon->billing_period, $addon->billing_interval) : null,
        ]);

        ActivityLogService::log('addon_purchased', $service, 'Client purchased addon: '.$addon->name);

        return redirect()->route('client.services.show', $service)
            ->with('success', 'Addon "'.$addon->name.'" has been added to your service');
    }
}
