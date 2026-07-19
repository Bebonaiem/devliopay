<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\Servers\PterodactylServer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestPterodactyl extends Command
{
    protected $signature = 'pterodactyl:test';
    protected $description = 'Test Pterodactyl panel connection and configuration';

    public function handle(): int
    {
        $host = Setting::get('pterodactyl_host', config('services.pterodactyl.host', ''));
        $apiKey = Setting::get('pterodactyl_api_key', config('services.pterodactyl.api_key', ''));

        $this->info('Pterodactyl Connection Test');
        $this->line('─────────────────────────────');
        $this->info('Host: ' . ($host ?: 'NOT SET'));
        $this->info('API Key: ' . ($apiKey ? substr($apiKey, 0, 10) . '...' : 'NOT SET'));

        if (empty($host) || empty($apiKey)) {
            $this->error('Host or API key is not configured!');
            return self::FAILURE;
        }

        // Test connection
        $this->line('');
        $this->info('Testing connection...');

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get(rtrim($host, '/') . '/api/application/servers?per_page=1');

            if ($response->successful()) {
                $this->info('Connection: OK');
                $data = $response->json();
                $this->info('Total servers: ' . ($data['meta']['total'] ?? count($data['data'] ?? [])));
            } else {
                $this->error('Connection failed: HTTP ' . $response->status());
                $this->error($response->body());
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Connection error: ' . $e->getMessage());
            return self::FAILURE;
        }

        // Test nodes
        $this->line('');
        $this->info('Fetching nodes...');

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get(rtrim($host, '/') . '/api/application/nodes?per_page=100');

            if ($response->successful()) {
                $nodes = $response->json('data', []);
                $this->info('Nodes found: ' . count($nodes));

                foreach ($nodes as $node) {
                    $attrs = $node['attributes'] ?? [];
                    $this->line("  - Node #{$attrs['id']}: {$attrs['name']} ({$attrs['fqd']})");

                    // Test allocations via node endpoint
                    $allocResult = Http::withoutVerifying()
                        ->withHeaders([
                            'Authorization' => 'Bearer ' . $apiKey,
                            'Accept' => 'application/json',
                        ])
                        ->get(rtrim($host, '/') . "/api/application/nodes/{$attrs['id']}/allocations?per_page=100");

                    if ($allocResult->successful()) {
                        $allocs = $allocResult->json('data', []);
                        $free = collect($allocs)->filter(fn ($a) => empty($a['attributes']['server_id']))->count();
                        $this->info("    Allocations: " . count($allocs) . " total, {$free} free");
                    } else {
                        $this->warn("    Allocations endpoint: HTTP {$allocResult->status()}");
                    }
                }
            } else {
                $this->error('Failed to fetch nodes: HTTP ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Node fetch error: ' . $e->getMessage());
        }

        // Test nests
        $this->line('');
        $this->info('Fetching nests...');

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get(rtrim($host, '/') . '/api/application/nests?per_page=100');

            if ($response->successful()) {
                $nests = $response->json('data', []);
                $this->info('Nests found: ' . count($nests));
                foreach ($nests as $nest) {
                    $this->line("  - Nest #{$nest['attributes']['id']}: {$nest['attributes']['name']}");
                }
            } else {
                $this->warn('Nests endpoint: HTTP ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Nest fetch error: ' . $e->getMessage());
        }

        $this->line('');
        $this->info('Test complete.');
        return self::SUCCESS;
    }
}
