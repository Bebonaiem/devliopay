<?php

namespace App\Services\Servers;

use App\Models\Service;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PterodactylServer implements ServerInterface
{
    private string $host;

    private string $apiKey;

    public function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        $configs = json_decode(Setting::get('server_configurations', '[]'), true);
        $config = collect($configs)->first(fn ($c) => ($c['type'] ?? '') === 'pterodactyl' && ! empty($c['is_active']));

        $this->host = $config['host']
            ?? Setting::get('pterodactyl_host', '')
            ?? config('services.pterodactyl.host', '')
            ?? '';

        $this->apiKey = $config['api_key']
            ?? Setting::get('pterodactyl_api_key', '')
            ?? config('services.pterodactyl.api_key', '')
            ?? '';
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        if (empty($this->host) || empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Pterodactyl panel not configured'];
        }

        $url = rtrim($this->host, '/').'/api/application'.$endpoint;

        try {
            $http = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(30);

            if (app()->environment('local', 'testing')) {
                $http = $http->withoutVerifying();
            }

            $response = $method === 'get'
                ? $http->get($url)
                : $http->$method($url, $data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('Pterodactyl API error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return ['success' => false, 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Pterodactyl API exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function createServer(Service $service, array $settings, array $properties): array
    {
        $eggId = (int) ($properties['egg_id'] ?? $settings['egg_id'] ?? 1);
        $nestId = (int) ($properties['nest_id'] ?? $settings['nest_id'] ?? 1);
        $nodeId = (int) ($properties['node_id'] ?? $settings['node_id'] ?? 1);
        $memory = (int) ($properties['ram'] ?? $settings['ram'] ?? 1024);
        $disk = (int) ($properties['disk'] ?? $settings['disk'] ?? 10240);
        $cpu = (int) ($properties['cpu'] ?? $settings['cpu'] ?? 100);
        $databases = (int) ($properties['databases'] ?? $settings['databases'] ?? 2);
        $allocations = (int) ($properties['allocations'] ?? $settings['allocations'] ?? 1);

        $userId = $this->getOrCreateUser($service->user);
        if ($userId === 0) {
            return ['success' => false, 'error' => 'Failed to create/find user in Pterodactyl'];
        }

        $allocationId = $this->getAllocation($nodeId);
        if ($allocationId === 0) {
            return ['success' => false, 'error' => 'No free IP:port allocations available on node '.$nodeId.'. Please add allocations in your Pterodactyl admin panel under Nodes → Settings → Allocations.'];
        }

        $eggData = $this->getEgg($eggId, $nestId);
        $dockerImage = $properties['docker_image']
            ?? $settings['docker_image']
            ?? $eggData['attributes']['docker_image']
            ?? 'ghcr.io/ptero-eggs/yolks:java_25';
        $startup = $eggData['attributes']['startup'] ?? 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar {{SERVER_JARFILE}}';

        $environment = $properties['environment'] ?? [];
        if (empty($environment)) {
            $environment = $this->getDefaultEnvironment($eggId, $nestId);
        }

        $payload = [
            'name' => $service->product->name ?? 'Game Server',
            'description' => 'Server for '.$service->user->email,
            'user' => $userId,
            'egg' => $eggId,
            'docker_image' => $dockerImage,
            'startup' => $startup,
            'nest' => $nestId,
            'node' => $nodeId,
            'start_on_completion' => true,
            'environment' => $environment,
            'limits' => [
                'memory' => $memory,
                'swap' => 0,
                'disk' => $disk,
                'io' => 500,
                'cpu' => $cpu,
            ],
            'feature_limits' => [
                'databases' => $databases,
                'allocations' => $allocations,
                'backups' => 0,
            ],
            'allocation' => [
                'default' => $allocationId,
            ],
        ];

        $result = $this->makeRequest('post', '/servers', $payload);

        if ($result['success']) {
            $attributes = $result['data']['attributes'] ?? [];
            $service->update([
                'external_id' => $attributes['external_id'] ?? null,
                'server_properties' => array_merge($service->server_properties ?? [], [
                    'server_id' => $attributes['id'] ?? null,
                    'identifier' => $attributes['identifier'] ?? null,
                    'ip_address' => $attributes['relationships']['allocations']['data'][0]['attributes']['ip'] ?? null,
                    'port' => $attributes['relationships']['allocations']['data'][0]['attributes']['port'] ?? null,
                ]),
            ]);

            return [
                'success' => true,
                'server_id' => $attributes['id'] ?? null,
                'identifier' => $attributes['identifier'] ?? null,
            ];
        }

        return ['success' => false, 'error' => $result['error'] ?? 'Failed to create server'];
    }

    private function getDefaultEnvironment(int $eggId, int $nestId = 1): array
    {
        $egg = $this->getEgg($eggId, $nestId);
        $vars = $egg['attributes']['relationships']['variables']['data'] ?? [];
        $env = ['SERVER_JARFILE' => 'server.jar', 'MINECRAFT_VERSION' => 'latest', 'BUILD_NUMBER' => 'latest'];
        if (! empty($vars)) {
            foreach ($vars as $variable) {
                $attr = $variable['attributes'];
                if (! empty($attr['default_value'])) {
                    $env[$attr['env_variable']] = $attr['default_value'];
                }
            }
        }

        return $env;
    }

    private function getEgg(int $eggId, int $nestId = 1): ?array
    {
        $result = $this->makeRequest('get', "/nests/{$nestId}/eggs/{$eggId}");

        if (! $result['success']) {
            $result = $this->makeRequest('get', "/eggs/{$eggId}");
        }

        return $result['success'] ? ($result['data'] ?? null) : null;
    }

    private function getOrCreateUser($user): int
    {
        if ($user->pterodactyl_user_id) {
            Log::info('Pterodactyl user already exists locally', ['user_id' => $user->pterodactyl_user_id]);
            if ($user->plain_password) {
                $user->update(['plain_password' => null]);
            }
            return $user->pterodactyl_user_id;
        }

        $result = $this->makeRequest('get', '/users?filter[email]='.urlencode($user->email));

        Log::info('Pterodactyl user search by email', [
            'email' => $user->email,
            'success' => $result['success'],
            'found' => ! empty($result['data']['data']),
        ]);

        if ($result['success'] && ! empty($result['data']['data'])) {
            $pteroUserId = $result['data']['data'][0]['attributes']['id'] ?? 0;
            if ($pteroUserId) {
                $user->update(['pterodactyl_user_id' => $pteroUserId]);
            }
            return $pteroUserId;
        }

        $username = Str::slug($user->name).'-'.Str::random(4);
        $firstName = explode(' ', $user->name)[0] ?? $user->name;
        $lastName = implode(' ', array_slice(explode(' ', $user->name), 1)) ?: '';

        $createResult = $this->makeRequest('post', '/users', [
            'email' => $user->email,
            'username' => $username,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => $user->plain_password ?? Str::random(16),
        ]);

        if ($user->plain_password) {
            $user->update(['plain_password' => null]);
        }

        Log::info('Pterodactyl user creation', [
            'email' => $user->email,
            'username' => $username,
            'success' => $createResult['success'],
            'error' => $createResult['success'] ? null : ($createResult['error'] ?? 'Unknown'),
        ]);

        if ($createResult['success']) {
            $pteroUserId = $createResult['data']['attributes']['id'] ?? 0;
            if ($pteroUserId) {
                $user->update(['pterodactyl_user_id' => $pteroUserId]);
            }
            return $pteroUserId;
        }

        Log::error('Failed to create Pterodactyl user', ['email' => $user->email, 'error' => $createResult['error'] ?? 'Unknown']);

        return 0;
    }

    private function getAllocation(int $nodeId): int
    {
        $result = $this->makeRequest('get', "/nodes/{$nodeId}/allocations?per_page=100");

        Log::info('Pterodactyl allocations response', [
            'node_id' => $nodeId,
            'success' => $result['success'],
            'count' => count($result['data']['data'] ?? []),
        ]);

        if ($result['success'] && ! empty($result['data']['data'])) {
            $available = collect($result['data']['data'])->first(function ($alloc) {
                $serverId = $alloc['attributes']['server_id'] ?? null;

                return $serverId === null || $serverId === 0 || $serverId === '';
            });

            if ($available) {
                return $available['attributes']['id'];
            }

            Log::warning('All allocations on node are in use', ['node_id' => $nodeId]);

            return 0;
        }

        Log::error('No allocations found on node', ['node_id' => $nodeId]);

        return 0;
    }

    public function suspendServer(Service $service, array $settings, array $properties): array
    {
        $serverId = $service->server_properties['server_id'] ?? null;
        if (! $serverId) {
            return ['success' => false, 'error' => 'No server ID found'];
        }

        $result = $this->makeRequest('post', "/servers/{$serverId}/suspend");

        return $result['success']
            ? ['success' => true]
            : ['success' => false, 'error' => $result['error'] ?? 'Failed to suspend server'];
    }

    public function unsuspendServer(Service $service, array $settings, array $properties): array
    {
        $serverId = $service->server_properties['server_id'] ?? null;
        if (! $serverId) {
            return ['success' => false, 'error' => 'No server ID found'];
        }

        $result = $this->makeRequest('post', "/servers/{$serverId}/unsuspend");

        return $result['success']
            ? ['success' => true]
            : ['success' => false, 'error' => $result['error'] ?? 'Failed to unsuspend server'];
    }

    public function terminateServer(Service $service, array $settings, array $properties): array
    {
        $serverId = $service->server_properties['server_id'] ?? null;
        if (! $serverId) {
            return ['success' => false, 'error' => 'No server ID found'];
        }

        $result = $this->makeRequest('delete', "/servers/{$serverId}/force");

        return $result['success']
            ? ['success' => true]
            : ['success' => false, 'error' => $result['error'] ?? 'Failed to terminate server'];
    }

    public function upgradeServer(Service $service, array $settings, array $properties): array
    {
        $serverId = $service->server_properties['server_id'] ?? null;
        if (! $serverId) {
            return ['success' => false, 'error' => 'No server ID found'];
        }

        $result = $this->makeRequest('patch', "/servers/{$serverId}/build", [
            'memory' => (int) ($properties['ram'] ?? $settings['ram'] ?? 1024),
            'disk' => (int) ($properties['disk'] ?? $settings['disk'] ?? 10240),
            'cpu' => (int) ($properties['cpu'] ?? $settings['cpu'] ?? 100),
        ]);

        return $result['success']
            ? ['success' => true]
            : ['success' => false, 'error' => $result['error'] ?? 'Failed to upgrade server'];
    }

    public function getServerInfo(Service $service): array
    {
        $serverId = $service->server_properties['server_id'] ?? null;
        if (! $serverId) {
            return ['success' => false, 'error' => 'No server ID found'];
        }

        $result = $this->makeRequest('get', "/servers/{$serverId}");
        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to get server info'];
        }

        $attrs = $result['data']['attributes'] ?? [];
        $allocations = $attrs['relationships']['allocations']['data'] ?? [];
        $alloc = $allocations[0]['attributes'] ?? [];

        $ipAddress = $alloc['ip'] ?? null;
        $port = $alloc['port'] ?? null;

        if (! $ipAddress) {
            $allocationId = $attrs['allocation'] ?? null;
            $nodeId = $attrs['node'] ?? null;
            if ($allocationId && $nodeId) {
                $page = 1;
                while ($page <= 10) {
                    $allocResult = $this->makeRequest('get', "/nodes/{$nodeId}/allocations?per_page=100&page={$page}");
                    if (! $allocResult['success'] || empty($allocResult['data']['data'])) {
                        break;
                    }
                    foreach ($allocResult['data']['data'] as $a) {
                        if (($a['attributes']['id'] ?? null) == $allocationId) {
                            $ipAddress = $a['attributes']['ip'] ?? null;
                            $port = $a['attributes']['port'] ?? null;
                            break 2;
                        }
                    }
                    $page++;
                }
            }
        }

        return [
            'success' => true,
            'status' => $attrs['status'] ?? 'unknown',
            'name' => $attrs['name'] ?? '',
            'description' => $attrs['description'] ?? '',
            'ip_address' => $ipAddress,
            'port' => $port,
            'memory' => $attrs['limits']['memory'] ?? 0,
            'disk' => $attrs['limits']['disk'] ?? 0,
            'cpu' => $attrs['limits']['cpu'] ?? 0,
            'node_id' => $attrs['node'] ?? null,
            'nest_id' => $attrs['nest'] ?? null,
            'egg_id' => $attrs['egg'] ?? null,
        ];
    }

    public function reinstallServer(Service $service, array $settings, array $properties): array
    {
        $serverId = $service->server_properties['server_id'] ?? null;
        if (! $serverId) {
            return ['success' => false, 'error' => 'No server ID found'];
        }

        $result = $this->makeRequest('post', "/servers/{$serverId}/reinstall");

        return $result['success']
            ? ['success' => true]
            : ['success' => false, 'error' => $result['error'] ?? 'Failed to reinstall server'];
    }

    public function getServerResources(Service $service): array
    {
        $serverId = $service->server_properties['server_id'] ?? null;
        if (! $serverId) {
            return ['success' => false, 'error' => 'No server ID found'];
        }

        $result = $this->makeRequest('get', "/servers/{$serverId}");
        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to get server resources'];
        }

        $attrs = $result['data']['attributes'] ?? [];
        $limits = $attrs['limits'] ?? [];
        $usage = $attrs['usage'] ?? [];

        return [
            'success' => true,
            'cpu' => $usage['cpu'] ?? 0,
            'memory' => $usage['memory'] ?? 0,
            'disk' => $usage['disk'] ?? 0,
            'cpu_limit' => $limits['cpu'] ?? 0,
            'memory_limit' => $limits['memory'] ?? 0,
            'disk_limit' => $limits['disk'] ?? 0,
            'status' => $attrs['status'] ?? 'unknown',
        ];
    }

    public function getPanelUrl(Service $service): ?string
    {
        if (empty($this->host)) {
            return null;
        }

        $identifier = $service->server_properties['identifier'] ?? $service->server_properties['server_id'] ?? null;
        if (! $identifier) {
            return null;
        }

        return rtrim($this->host, '/').'/server/'.$identifier;
    }

    public function getProductConfig(array $values = []): array
    {
        return [
            [
                'name' => 'egg_id',
                'label' => 'Egg ID',
                'type' => 'number',
                'required' => true,
                'description' => 'The Pterodactyl egg ID for this server type',
            ],
            [
                'name' => 'nest_id',
                'label' => 'Nest ID',
                'type' => 'number',
                'required' => true,
                'default' => 1,
                'description' => 'The Pterodactyl nest ID (game category)',
            ],
            [
                'name' => 'node_id',
                'label' => 'Node ID',
                'type' => 'number',
                'required' => true,
                'description' => 'The Pterodactyl node to deploy to',
            ],
            [
                'name' => 'ram',
                'label' => 'RAM (MB)',
                'type' => 'number',
                'required' => true,
                'default' => 1024,
            ],
            [
                'name' => 'disk',
                'label' => 'Disk (MB)',
                'type' => 'number',
                'required' => true,
                'default' => 10240,
            ],
            [
                'name' => 'cpu',
                'label' => 'CPU (%)',
                'type' => 'number',
                'required' => true,
                'default' => 100,
            ],
            [
                'name' => 'databases',
                'label' => 'Databases',
                'type' => 'number',
                'default' => 2,
            ],
            [
                'name' => 'allocations',
                'label' => 'Allocations',
                'type' => 'number',
                'default' => 1,
            ],
        ];
    }

    public function getCheckoutConfig($product, array $values = [], array $settings = []): array
    {
        return [
            [
                'name' => 'server_name',
                'label' => 'Server Name',
                'type' => 'text',
                'required' => true,
                'description' => 'A name for your game server',
            ],
        ];
    }

    public function getNests(): array
    {
        $result = $this->makeRequest('get', '/nests?per_page=100');
        if (! $result['success']) {
            return [];
        }

        return collect($result['data']['data'] ?? [])
            ->mapWithKeys(fn ($nest) => [$nest['attributes']['id'] => $nest['attributes']['name']])
            ->toArray();
    }

    public function getEggs(int $nestId): array
    {
        $result = $this->makeRequest('get', "/nests/{$nestId}/eggs?per_page=100");
        if (! $result['success']) {
            return [];
        }

        return collect($result['data']['data'] ?? [])
            ->mapWithKeys(fn ($egg) => [$egg['attributes']['id'] => $egg['attributes']['name']])
            ->toArray();
    }

    public function getNodes(): array
    {
        $result = $this->makeRequest('get', '/nodes?per_page=100');
        if (! $result['success']) {
            return [];
        }

        return collect($result['data']['data'] ?? [])
            ->mapWithKeys(fn ($node) => [$node['attributes']['id'] => $node['attributes']['name']])
            ->toArray();
    }

    public function getActions(Service $service, array $settings, array $properties): array
    {
        $actions = [];

        if ($service->status === 'active') {
            $actions[] = [
                'name' => 'control_panel',
                'label' => 'Control Panel',
                'url' => rtrim($this->host, '/').'/server/'.($service->server_properties['identifier'] ?? $service->server_properties['server_id'] ?? ''),
                'type' => 'button',
            ];
        }

        return $actions;
    }

    public function getAllocations(Service $service): array
    {
        $serverId = $service->server_properties['server_id'] ?? null;
        if (! $serverId) {
            return ['success' => false, 'error' => 'No server ID found'];
        }

        $result = $this->makeRequest('get', "/servers/{$serverId}?include=allocations");
        if (! $result['success']) {
            return ['success' => false, 'error' => $result['error'] ?? 'Failed to get allocations'];
        }

        $allocations = collect($result['data']['attributes']['relationships']['allocations']['data'] ?? [])
            ->map(fn ($a) => [
                'id' => $a['attributes']['id'],
                'ip' => $a['attributes']['ip'],
                'port' => $a['attributes']['port'],
                'notes' => $a['attributes']['notes'] ?? '',
            ])->toArray();

        return ['success' => true, 'allocations' => $allocations];
    }
}
