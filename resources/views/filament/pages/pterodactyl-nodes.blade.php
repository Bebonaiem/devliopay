<x-filament-panels::page>
    <div class="space-y-6">
        @if($error)
            <div class="rounded-xl bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 p-4 text-sm text-danger-700 dark:text-danger-400">
                {{ $error }}
            </div>
        @endif

        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Nodes</h2>
            <x-filament::button wire:click="refreshData" color="gray" size="sm">
                <x-heroicon-o-arrow-path class="w-4 h-4" />
                Refresh
            </x-filament::button>
        </div>

        @if(empty($nodes) && empty($error))
            <div class="text-center py-12 text-gray-500">No nodes found. Check your Pterodactyl configuration.</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($nodes as $node)
                <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-white/10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/50">
                            <x-heroicon-o-server-stack class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <div class="font-semibold">{{ $node['name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $node['host'] }}</div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500">Memory</span>
                                <span>{{ number_format($node['memory_used'] / 1024, 1) }} / {{ number_format($node['memory'] / 1024, 1) }} GB</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $this->getNodeMemoryPercentage($node) > 80 ? 'bg-danger-500' : 'bg-primary-500' }}"
                                     style="width: {{ min($this->getNodeMemoryPercentage($node), 100) }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500">Disk</span>
                                <span>{{ number_format($node['disk_used'] / 1024, 1) }} / {{ number_format($node['disk'] / 1024, 1) }} GB</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $this->getNodeDiskPercentage($node) > 80 ? 'bg-danger-500' : 'bg-success-500' }}"
                                     style="width: {{ min($this->getNodeDiskPercentage($node), 100) }}%"></div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <x-filament::badge :color="match($node['status']) {
                                'online' => 'success',
                                'offline' => 'danger',
                                default => 'gray',
                            }" size="xs">{{ ucfirst($node['status']) }}</x-filament::badge>
                            @if($node['is_public'])
                                <x-filament::badge color="info" size="xs">Public</x-filament::badge>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-8 text-gray-500">No nodes found</div>
                </div>
            @endforelse
        </div>

        @php
            $paginated = $this->getPaginatedAllocations();
        @endphp

        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-list-bullet class="w-5 h-5 text-gray-400" />
                    <span>Allocations ({{ $paginated['total'] }})</span>
                </div>
            </x-slot>

            <x-slot name="headerActions">
            <x-filament::button wire:click="refreshData" color="gray" size="sm">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                </x-filament::button>
            </x-slot>

            {{-- Filters --}}
            <div class="flex flex-wrap items-end gap-3 mb-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs font-medium text-gray-500 mb-1 block">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           placeholder="IP, port, node, server, notes..."
                           class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" />
                </div>

                <div class="min-w-[180px]">
                    <label class="text-xs font-medium text-gray-500 mb-1 block">Node</label>
                    <select wire:model.live="filterNode"
                            class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none dark:[color-scheme:dark]">
                        <option value="">All Nodes</option>
                        @foreach($this->getNodeOptions() as $nodeId => $nodeName)
                            <option value="{{ $nodeId }}">{{ $nodeName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-[150px]">
                    <label class="text-xs font-medium text-gray-500 mb-1 block">Status</label>
                    <select wire:model.live="filterStatus"
                            class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none dark:[color-scheme:dark]">
                        <option value="">All</option>
                        <option value="assigned">Assigned</option>
                        <option value="free">Free</option>
                    </select>
                </div>

                <div class="min-w-[120px]">
                    <label class="text-xs font-medium text-gray-500 mb-1 block">Per Page</label>
                    <select wire:model.live="allocPerPage"
                            class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none dark:[color-scheme:dark]">
                        <option value="10" style="background:#1a1c2e;color:#fff">10</option>
                        <option value="25" style="background:#1a1c2e;color:#fff">25</option>
                        <option value="50" style="background:#1a1c2e;color:#fff">50</option>
                        <option value="100" style="background:#1a1c2e;color:#fff">100</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="text-left py-2 font-medium text-gray-500">#</th>
                            <th class="text-left py-2 font-medium text-gray-500">Node</th>
                            <th class="text-left py-2 font-medium text-gray-500">IP</th>
                            <th class="text-left py-2 font-medium text-gray-500">Port</th>
                            <th class="text-left py-2 font-medium text-gray-500">Assigned</th>
                            <th class="text-left py-2 font-medium text-gray-500">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginated['items'] as $i => $alloc)
                            @php $rowNum = $paginated['from'] + $i; @endphp
                            <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="py-2 text-gray-400 text-xs">{{ $rowNum }}</td>
                                <td class="py-2">{{ $alloc['node_name'] }}</td>
                                <td class="py-2 font-mono text-xs">{{ $alloc['ip'] }}</td>
                                <td class="py-2 font-mono text-xs">{{ $alloc['port'] }}</td>
                                <td class="py-2">
                                    @if($alloc['server_id'] && $alloc['server_name'])
                                        <x-filament::badge color="success" size="xs">{{ $alloc['server_name'] }}</x-filament::badge>
                                    @else
                                        <x-filament::badge color="gray" size="xs">Free</x-filament::badge>
                                    @endif
                                </td>
                                <td class="py-2 text-gray-500 text-xs">{{ $alloc['notes'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-gray-500">No allocations match your filters</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($paginated['totalPages'] > 1)
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200 dark:border-white/10">
                    <div class="text-xs text-gray-500">
                        Showing {{ $paginated['from'] }}-{{ $paginated['to'] }} of {{ $paginated['total'] }}
                    </div>
                    <div class="flex items-center gap-1">
                        <button wire:click="setPage(1)" wire:loading.attr="disabled"
                                {{ $paginated['currentPage'] <= 1 ? 'disabled' : '' }}
                                class="px-2 py-1 text-xs rounded {{ $paginated['currentPage'] <= 1 ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                            &laquo;
                        </button>
                        <button wire:click="setPage({{ $paginated['currentPage'] - 1 }})" wire:loading.attr="disabled"
                                {{ $paginated['currentPage'] <= 1 ? 'disabled' : '' }}
                                class="px-2 py-1 text-xs rounded {{ $paginated['currentPage'] <= 1 ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                            &lsaquo;
                        </button>

                        @php
                            $start = max(1, $paginated['currentPage'] - 2);
                            $end = min($paginated['totalPages'], $paginated['currentPage'] + 2);
                        @endphp

                        @if($start > 1)
                            <span class="px-2 py-1 text-xs text-gray-400">...</span>
                        @endif

                        @for($p = $start; $p <= $end; $p++)
                            <button wire:click="setPage({{ $p }})"
                                    class="px-3 py-1 text-xs rounded {{ $p === $paginated['currentPage'] ? 'bg-primary-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                                {{ $p }}
                            </button>
                        @endfor

                        @if($end < $paginated['totalPages'])
                            <span class="px-2 py-1 text-xs text-gray-400">...</span>
                        @endif

                        <button wire:click="setPage({{ $paginated['currentPage'] + 1 }})" wire:loading.attr="disabled"
                                {{ $paginated['currentPage'] >= $paginated['totalPages'] ? 'disabled' : '' }}
                                class="px-2 py-1 text-xs rounded {{ $paginated['currentPage'] >= $paginated['totalPages'] ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                            &rsaquo;
                        </button>
                        <button wire:click="setPage({{ $paginated['totalPages'] }})" wire:loading.attr="disabled"
                                {{ $paginated['currentPage'] >= $paginated['totalPages'] ? 'disabled' : '' }}
                                class="px-2 py-1 text-xs rounded {{ $paginated['currentPage'] >= $paginated['totalPages'] ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10' }}">
                            &raquo;
                        </button>
                    </div>
                </div>
            @elseif($paginated['total'] > 0)
                <div class="text-xs text-gray-500 mt-4 pt-4 border-t border-gray-200 dark:border-white/10">
                    Showing {{ $paginated['from'] }}-{{ $paginated['to'] }} of {{ $paginated['total'] }}
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
