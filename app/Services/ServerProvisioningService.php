<?php

namespace App\Services;

use App\Models\Service;
use App\Notifications\ServiceActivated;
use App\Notifications\ServiceStatusChanged;
use App\Notifications\ServiceSuspended;
use App\Services\Servers\ServerFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class ServerProvisioningService
{
    public function provision(Service $service): array
    {
        $server = ServerFactory::make($service->server_extension);
        if (! $server) {
            return ['success' => false, 'error' => 'Server extension not found: '.$service->server_extension];
        }

        $productConfig = $service->product->config_options ?? [];
        $serviceConfig = $service->config_options ?? [];
        $mergedConfig = array_merge($productConfig, $serviceConfig);

        if (empty($serviceConfig)) {
            $service->update(['config_options' => $productConfig]);
        }

        $settings = $productConfig;
        $properties = array_merge($serviceConfig, $service->server_properties ?? []);

        try {
            $result = $server->createServer($service, $settings, $properties);

            if ($result['success']) {
                $service->update(['status' => 'active', 'activated_at' => now()]);
                App::make(NotificationService::class)->notify($service->user, new ServiceActivated($service));
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Server provisioning failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function suspend(Service $service): array
    {
        $server = ServerFactory::make($service->server_extension);
        if (! $server) {
            return ['success' => false, 'error' => 'Server extension not found'];
        }

        $settings = [];
        $properties = $service->server_properties ?? [];

        try {
            $result = $server->suspendServer($service, $settings, $properties);

            if ($result['success']) {
                $service->update(['status' => 'suspended', 'suspended_at' => now()]);
                App::make(NotificationService::class)->notify($service->user, new ServiceSuspended($service));
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Server suspension failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function unsuspend(Service $service): array
    {
        $server = ServerFactory::make($service->server_extension);
        if (! $server) {
            return ['success' => false, 'error' => 'Server extension not found'];
        }

        $settings = [];
        $properties = $service->server_properties ?? [];

        try {
            $result = $server->unsuspendServer($service, $settings, $properties);

            if ($result['success']) {
                $oldStatus = $service->status;
                $service->update(['status' => 'active', 'suspended_at' => null]);
                App::make(NotificationService::class)->notify($service->user, new ServiceStatusChanged($service, $oldStatus, 'active'));
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Server unsuspend failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function terminate(Service $service): array
    {
        $server = ServerFactory::make($service->server_extension);
        if (! $server) {
            return ['success' => false, 'error' => 'Server extension not found'];
        }

        $settings = [];
        $properties = $service->server_properties ?? [];

        try {
            $result = $server->terminateServer($service, $settings, $properties);

            if ($result['success']) {
                $oldStatus = $service->status;
                $service->update(['status' => 'terminated', 'terminated_at' => now()]);
                App::make(NotificationService::class)->notify($service->user, new ServiceStatusChanged($service, $oldStatus, 'terminated'));
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Server termination failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function upgrade(Service $service, array $newProperties): array
    {
        $server = ServerFactory::make($service->server_extension);
        if (! $server) {
            return ['success' => false, 'error' => 'Server extension not found'];
        }

        $settings = $service->product->config_options ?? [];
        $properties = array_merge($service->server_properties ?? [], $newProperties);

        try {
            $result = $server->upgradeServer($service, $settings, $properties);

            if ($result['success']) {
                $service->update(['server_properties' => $properties]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Server upgrade failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function reinstall(Service $service): array
    {
        $server = ServerFactory::make($service->server_extension);
        if (! $server) {
            return ['success' => false, 'error' => 'Server extension not found'];
        }

        $settings = $service->product->config_options ?? [];
        $properties = $service->server_properties ?? [];

        try {
            return $server->reinstallServer($service, $settings, $properties);
        } catch (\Exception $e) {
            Log::error('Server reinstall failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getServerInfo(Service $service): array
    {
        $server = ServerFactory::make($service->server_extension);
        if (! $server) {
            return ['success' => false, 'error' => 'Server extension not found'];
        }

        try {
            return $server->getServerInfo($service);
        } catch (\Exception $e) {
            Log::error('Server info fetch failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getServerResources(Service $service): array
    {
        $server = ServerFactory::make($service->server_extension);
        if (! $server) {
            return ['success' => false, 'error' => 'Server extension not found'];
        }

        try {
            return $server->getServerResources($service);
        } catch (\Exception $e) {
            Log::error('Server resources fetch failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPanelUrl(Service $service): ?string
    {
        $server = ServerFactory::make($service->server_extension);
        if (! $server) {
            return null;
        }

        try {
            return $server->getPanelUrl($service);
        } catch (\Exception $e) {
            return null;
        }
    }
}
