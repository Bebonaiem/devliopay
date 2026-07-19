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
        $synced = 0;
        $failed = 0;

        Service::where('server_extension', 'pterodactyl')
            ->whereNotIn('status', ['cancelled', 'terminated'])
            ->chunk(50, function ($services) use (&$synced, &$failed) {
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
                    $productName = $service->product?->name ?? 'Unknown';
                    $this->line("Synced service #{$service->id} ({$productName}): {$result['status']}");
                    $synced++;
                }
            });

        if ($synced === 0 && $failed === 0) {
            $this->info('No Pterodactyl services to sync.');
            return self::SUCCESS;
        }

        $this->info("Sync complete: {$synced} synced, {$failed} failed.");
        return self::SUCCESS;
    }
}
