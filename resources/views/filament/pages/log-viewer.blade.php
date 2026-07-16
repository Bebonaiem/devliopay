<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Log File</label>
                <select wire:model.live="selectedLog" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @foreach($logFiles as $file)
                        <option value="{{ $file }}">{{ $file }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 block">Filter Level</label>
                <div class="flex gap-1">
                    @foreach(['all', 'debug', 'info', 'warning', 'error', 'critical'] as $level)
                        <button wire:click="setFilterLevel('{{ $level }}')"
                                class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                                       {{ $filterLevel === $level
                                           ? 'bg-primary-600 text-white'
                                           : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            {{ ucfirst($level) }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-xs text-gray-500">
            Showing {{ count($logs) }} entries from {{ $selectedLog }}
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-auto max-h-[70vh]">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800 z-10">
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="text-left py-2 px-3 font-medium text-gray-500 w-48">Timestamp</th>
                            <th class="text-left py-2 px-3 font-medium text-gray-500 w-24">Level</th>
                            <th class="text-left py-2 px-3 font-medium text-gray-500">Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $entry)
                            <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="py-2 px-3 font-mono text-xs text-gray-500 whitespace-nowrap">{{ $entry['timestamp'] }}</td>
                                <td class="py-2 px-3">
                                    @php
                                        $levelColor = match(strtolower($entry['level'])) {
                                            'debug' => 'gray',
                                            'info' => 'info',
                                            'notice' => 'info',
                                            'warning' => 'warning',
                                            'error' => 'danger',
                                            'critical' => 'danger',
                                            'alert' => 'danger',
                                            'emergency' => 'danger',
                                            default => 'gray',
                                        };
                                    @endphp
                                    <x-filament::badge :color="$levelColor" size="xs">{{ $entry['level'] }}</x-filament::badge>
                                </td>
                                <td class="py-2 px-3 text-xs break-all">
                                    @if(strlen($entry['message']) > 300)
                                        <span title="{{ $entry['message'] }}">{{ substr($entry['message'], 0, 300) }}...</span>
                                    @else
                                        {{ $entry['message'] }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-gray-500">No log entries found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
