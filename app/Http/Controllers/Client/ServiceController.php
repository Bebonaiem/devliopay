<?php

namespace App\Http\Controllers\Client;

use App\Models\Addon;
use App\Models\Service;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\Servers\PterodactylServer;
use App\Services\ServerProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ServiceController extends Controller
{
    public function index()
    {
        $services = auth()->user()->services()
            ->with(['product', 'pricing'])
            ->orderByDesc('id')
            ->get();

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

        return view('client.services.show', compact('service', 'panelUrl'));
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
}
