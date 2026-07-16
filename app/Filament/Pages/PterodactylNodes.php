<?php

namespace App\Filament\Pages;

use App\Services\Servers\PterodactylServer;
use Filament\Pages\Page;

class PterodactylNodes extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Pterodactyl Nodes';

    protected static ?string $title = 'Pterodactyl Nodes & Allocations';

    protected static string $view = 'filament.pages.pterodactyl-nodes';

    public array $nodes = [];

    public array $allocations = [];

    public string $error = '';

    public string $search = '';

    public string $filterNode = '';

    public string $filterStatus = '';

    public int $allocPage = 1;

    public int $allocPerPage = 25;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->error = '';

        try {
            $pterodactyl = new PterodactylServer();

            $nodesResult = $this->fetchNodes($pterodactyl);
            $this->nodes = $nodesResult;

            $this->allocations = $this->fetchAllAllocations($pterodactyl, $nodesResult);
        } catch (\Exception $e) {
            $this->error = 'Failed to connect to Pterodactyl panel: '.$e->getMessage();
        }
    }

    public function updatedFilterNode(): void
    {
        $this->allocPage = 1;
    }

    public function updatedFilterStatus(): void
    {
        $this->allocPage = 1;
    }

    public function updatedSearch(): void
    {
        $this->allocPage = 1;
    }

    public function getFilteredAllocations(): array
    {
        $allocs = $this->allocations;

        if ($this->search !== '') {
            $s = strtolower($this->search);
            $allocs = array_values(array_filter($allocs, fn ($a) =>
                str_contains(strtolower($a['ip']), $s)
                || str_contains((string) $a['port'], $s)
                || str_contains(strtolower($a['node_name'] ?? ''), $s)
                || str_contains(strtolower($a['server_name'] ?? ''), $s)
                || str_contains(strtolower($a['notes'] ?? ''), $s)
            ));
        }

        if ($this->filterNode !== '') {
            $allocs = array_values(array_filter($allocs, fn ($a) => (string) $a['node_id'] === $this->filterNode));
        }

        if ($this->filterStatus === 'assigned') {
            $allocs = array_values(array_filter($allocs, fn ($a) => ! empty($a['server_id'])));
        } elseif ($this->filterStatus === 'free') {
            $allocs = array_values(array_filter($allocs, fn ($a) => empty($a['server_id'])));
        }

        return $allocs;
    }

    public function getPaginatedAllocations(): array
    {
        $filtered = $this->getFilteredAllocations();
        $total = count($filtered);
        $totalPages = max(1, (int) ceil($total / $this->allocPerPage));

        if ($this->allocPage > $totalPages) {
            $this->allocPage = $totalPages;
        }

        $offset = ($this->allocPage - 1) * $this->allocPerPage;

        return [
            'items' => array_slice($filtered, $offset, $this->allocPerPage),
            'total' => $total,
            'totalPages' => $totalPages,
            'currentPage' => $this->allocPage,
            'from' => $total > 0 ? $offset + 1 : 0,
            'to' => min($offset + $this->allocPerPage, $total),
        ];
    }

    public function getNodeOptions(): array
    {
        return collect($this->nodes)
            ->mapWithKeys(fn ($n) => [$n['id'] => $n['name']])
            ->toArray();
    }

    public function setPage(int $page): void
    {
        $this->allocPage = $page;
    }

    public function getNodeMemoryPercentage(array $node): float
    {
        if ($node['memory'] <= 0) {
            return 0;
        }

        return round(($node['memory_used'] / $node['memory']) * 100, 1);
    }

    public function getNodeDiskPercentage(array $node): float
    {
        if ($node['disk'] <= 0) {
            return 0;
        }

        return round(($node['disk_used'] / $node['disk']) * 100, 1);
    }

    private function fetchNodes(PterodactylServer $pterodactyl): array
    {
        $reflection = new \ReflectionClass($pterodactyl);
        $method = $reflection->getMethod('makeRequest');
        $method->setAccessible(true);

        $result = $method->invoke($pterodactyl, 'get', '/nodes?per_page=100');

        if (! $result['success']) {
            $this->error = 'Failed to fetch nodes: '.($result['error'] ?? 'Unknown error');

            return [];
        }

        return collect($result['data']['data'] ?? [])
            ->map(fn ($node) => [
                'id' => $node['attributes']['id'],
                'name' => $node['attributes']['name'],
                'host' => $node['attributes']['fqdn'] ?? $node['attributes']['host'] ?? $node['attributes']['name'],
                'scheme' => $node['attributes']['scheme'] ?? 'https',
                'http_port' => $node['attributes']['http_port'] ?? 8080,
                'memory' => $node['attributes']['memory'] ?? 0,
                'disk' => $node['attributes']['disk'] ?? 0,
                'memory_used' => $node['attributes']['allocated_resources']['memory'] ?? 0,
                'disk_used' => $node['attributes']['allocated_resources']['disk'] ?? 0,
                'is_public' => $node['attributes']['is_public'] ?? false,
                'status' => $node['attributes']['status'] ?? 'unknown',
            ])
            ->toArray();
    }

    private function fetchAllAllocations(PterodactylServer $pterodactyl, array $nodes): array
    {
        $allocations = [];
        $reflection = new \ReflectionClass($pterodactyl);
        $method = $reflection->getMethod('makeRequest');
        $method->setAccessible(true);

        foreach ($nodes as $node) {
            $page = 1;
            while ($page <= 10) {
                $result = $method->invoke($pterodactyl, 'get', "/nodes/{$node['id']}/allocations?per_page=100&page={$page}");

                if (! $result['success'] || empty($result['data']['data'])) {
                    break;
                }

                foreach ($result['data']['data'] as $alloc) {
                    $attrs = $alloc['attributes'];
                    $allocations[] = [
                        'id' => $attrs['id'],
                        'node_id' => $node['id'],
                        'node_name' => $node['name'],
                        'ip' => $attrs['ip'],
                        'port' => $attrs['port'],
                        'notes' => $attrs['notes'] ?? '',
                        'server_id' => $attrs['server_id'] ?? null,
                        'server_name' => null,
                    ];
                }

                if (count($result['data']['data']) < 100) {
                    break;
                }
                $page++;
            }
        }

        $serverIds = collect($allocations)->pluck('server_id')->filter()->unique()->values();
        if ($serverIds->isNotEmpty()) {
            $result = $method->invoke($pterodactyl, 'get', '/servers?per_page=100');
            if ($result['success']) {
                $serversMap = collect($result['data']['data'] ?? [])
                    ->mapWithKeys(fn ($s) => [
                        $s['attributes']['id'] => $s['attributes']['name'] ?? 'Server #'.$s['attributes']['id'],
                    ])
                    ->toArray();

                foreach ($allocations as &$alloc) {
                    if ($alloc['server_id'] && isset($serversMap[$alloc['server_id']])) {
                        $alloc['server_name'] = $serversMap[$alloc['server_id']];
                    }
                }
                unset($alloc);
            }
        }

        return $allocations;
    }
}
