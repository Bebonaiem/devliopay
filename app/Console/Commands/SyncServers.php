<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Services\Servers\ServerFactory;
use Illuminate\Console\Command;

class SyncServers extends Command
{
    protected $signature = 'servers:sync';
    protected $description = 'Sync server statuses from Pterodactyl panel';

    public function handle(): int
    {
        $services = Service::where('server_extension', 'pterodactyl')
            ->whereNotIn('status', ['cancelled', 'terminated'])
            ->get();

        if ($services->isEmpty()) {
            $this->info('No Pterodactyl services to sync.');
            return self::SUCCESS;
        }

        $synced = 0;
        $failed = 0;

        foreach ($services as $service) {
            $server = ServerFactory::make($service->server_extension);
            if (!$server) {
                $this->warn("No server driver for service #{$service->id}");
                $failed++;
                continue;
            }

            $result = $server->getServerInfo($service);

            if (!$result['success']) {
                $this->warn("Failed to sync service #{$service->id}: {$result['error']}");
                $failed++;
                continue;
            }

            $newStatus = match ($result['status']) {
                'running' => 'active',
                'stopped' => 'suspended',
                'starting' => 'active',
                'stopping' => 'suspended',
                'installing' => 'pending',
                'suspended' => 'suspended',
                default => $service->status,
            };

            $updates = [
                'server_properties' => array_merge($service->server_properties ?? [], [
                    'status' => $result['status'],
                    'ip_address' => $result['ip_address'] ?? $service->server_properties['ip_address'] ?? null,
                    'port' => $result['port'] ?? $service->server_properties['port'] ?? null,
                ]),
            ];

            if ($newStatus !== $service->status && !in_array($service->status, ['cancelled', 'terminated'])) {
                $updates['status'] = $newStatus;
            }

            $service->update($updates);
            $this->line("Synced service #{$service->id} ({$service->product->name ?? 'Unknown'}): {$result['status']}");
            $synced++;
        }

        $this->info("Sync complete: {$synced} synced, {$failed} failed.");
        return self::SUCCESS;
    }
}
