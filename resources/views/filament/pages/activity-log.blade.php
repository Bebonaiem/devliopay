<x-filament::page>
    <div class="space-y-4">
        <div class="flex gap-2 flex-wrap">
            <button wire:click="$set('filterType', null)"
                    class="px-3 py-1 text-sm rounded-lg {{ is_null($filterType) ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-white/10 text-white hover:bg-gray-300 dark:hover:bg-white/20' }}">
                All
            </button>
            @foreach($this->getTypes() as $type)
                <button wire:click="$set('filterType', '{{ $type }}')"
                        class="px-3 py-1 text-sm rounded-lg {{ $filterType === $type ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-white/10 text-white hover:bg-gray-300 dark:hover:bg-white/20' }}">
                    {{ ucwords(str_replace('_', ' ', $type)) }}
                </button>
            @endforeach
        </div>

        @php $logs = $this->getLogs(); @endphp

        <div class="bg-white dark:bg-white/5 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-4 text-sm text-white">{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-white">{{ $log->user->name ?? 'System' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full font-medium
                                    {{ match(true) {
                                        str_contains($log->type, 'created') => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
                                        str_contains($log->type, 'suspended') => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
                                        str_contains($log->type, 'terminated') => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                        default => 'bg-gray-100 dark:bg-white/10 text-gray-800 dark:text-gray-400',
                                    } }}">
                                    {{ ucwords(str_replace('_', ' ', $log->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-white max-w-xs truncate">{{ $log->description }}</td>
                            <td class="px-6 py-4 text-sm text-white font-mono">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-white">
                                <x-heroicon-o-clock class="w-12 h-12 mx-auto text-white/30 mb-3" />
                                No activity logs found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center">
            <span class="text-sm text-white">
                Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
            </span>
            <div class="flex gap-1">
                @if($logs->previousPageUrl())
                    <button wire:click="previousPage" class="px-3 py-1 text-sm rounded-lg bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                    </button>
                @endif
                @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                    <button wire:click="gotoPage({{ $page }})" class="px-3 py-1 text-sm rounded-lg {{ $logs->currentPage() === $page ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20' }}">
                        {{ $page }}
                    </button>
                @endforeach
                @if($logs->nextPageUrl())
                    <button wire:click="nextPage" class="px-3 py-1 text-sm rounded-lg bg-gray-200 dark:bg-white/10 hover:bg-gray-300 dark:hover:bg-white/20">
                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                    </button>
                @endif
            </div>
        </div>
    </div>
</x-filament::page>
