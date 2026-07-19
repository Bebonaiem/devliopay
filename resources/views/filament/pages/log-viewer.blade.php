<x-filament::page>
    <div class="space-y-4">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="text-sm font-medium text-white mb-1 block">Log File</label>
                <select wire:model.live="selectedLog"
                        class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-white shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none dark:[color-scheme:dark]">
                    @foreach($logFiles as $file)
                        <option value="{{ $file }}">{{ $file }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-white mb-1 block">Filter Level</label>
                <div class="flex gap-1.5">
                    @php
                        $levels = [
                            'all' => ['label' => 'All', 'active' => 'bg-primary-600 text-white', 'inactive' => 'bg-white dark:bg-white/10 text-white hover:bg-gray-200 dark:hover:bg-white/20 border border-gray-300 dark:border-white/10'],
                            'debug' => ['label' => 'Debug', 'active' => 'bg-gray-500 text-white', 'inactive' => 'bg-white dark:bg-white/10 text-white hover:bg-gray-200 dark:hover:bg-white/20 border border-gray-300 dark:border-white/10'],
                            'info' => ['label' => 'Info', 'active' => 'bg-blue-600 text-white', 'inactive' => 'bg-white dark:bg-white/10 text-white hover:bg-gray-200 dark:hover:bg-white/20 border border-gray-300 dark:border-white/10'],
                            'warning' => ['label' => 'Warning', 'active' => 'bg-amber-500 text-white', 'inactive' => 'bg-white dark:bg-white/10 text-white hover:bg-gray-200 dark:hover:bg-white/20 border border-gray-300 dark:border-white/10'],
                            'error' => ['label' => 'Error', 'active' => 'bg-red-600 text-white', 'inactive' => 'bg-white dark:bg-white/10 text-white hover:bg-gray-200 dark:hover:bg-white/20 border border-gray-300 dark:border-white/10'],
                            'critical' => ['label' => 'Critical', 'active' => 'bg-rose-700 text-white', 'inactive' => 'bg-white dark:bg-white/10 text-white hover:bg-gray-200 dark:hover:bg-white/20 border border-gray-300 dark:border-white/10'],
                        ];
                    @endphp
                    @foreach($levels as $key => $config)
                        <button wire:click="setFilterLevel('{{ $key }}')"
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all {{ $filterLevel === $key ? $config['active'] : $config['inactive'] }}">
                            {{ $config['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-xs text-white">
            Showing {{ count($logs) }} entries from {{ $selectedLog }}
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-auto max-h-[70vh]">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-white/5 z-10">
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="text-left py-2 px-3 font-medium text-white w-48">Timestamp</th>
                            <th class="text-left py-2 px-3 font-medium text-white w-24">Level</th>
                            <th class="text-left py-2 px-3 font-medium text-white">Message</th>
                            <th class="text-center py-2 px-3 font-medium text-white w-16">Copy</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($logs as $entry)
                            @php
                                $levelBadge = match(strtolower($entry['level'])) {
                                    'debug' => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
                                    'info' => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
                                    'notice' => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
                                    'warning' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
                                    'error' => 'bg-red-500/10 text-red-400 border border-red-500/20',
                                    'critical' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                                    'alert' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                                    'emergency' => 'bg-rose-500/10 text-rose-400 border border-rose-500/20',
                                    default => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="py-2 px-3 font-mono text-xs text-white whitespace-nowrap">{{ $entry['timestamp'] }}</td>
                                <td class="py-2 px-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $levelBadge }}">
                                        {{ $entry['level'] }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-xs text-white break-all">
                                    @if(strlen($entry['message']) > 300)
                                        <span title="{{ $entry['message'] }}">{{ substr($entry['message'], 0, 300) }}...</span>
                                    @else
                                        {{ $entry['message'] }}
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <button x-data="{ copied: false }"
                                            x-on:click="
                                                navigator.clipboard.writeText(@js($entry['message']));
                                                copied = true;
                                                setTimeout(() => copied = false, 1500);
                                            "
                                            x-tooltip="copied ? 'Copied!' : 'Copy message'"
                                            class="p-1.5 rounded-lg transition-all hover:bg-white/10 text-gray-400 hover:text-white">
                                        <x-heroicon-o-clipboard x-show="!copied" class="w-4 h-4" />
                                        <x-heroicon-o-check x-show="copied" class="w-4 h-4 text-emerald-400" />
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-white">
                                    <x-heroicon-o-document-text class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                    No log entries found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament::page>
