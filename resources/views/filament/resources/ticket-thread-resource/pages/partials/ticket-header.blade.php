@php
    $statusColors = [
        'open' => 'warning',
        'answered' => 'success',
        'closed' => 'gray',
    ];
    $priorityColors = [
        'low' => 'gray',
        'medium' => 'info',
        'high' => 'warning',
        'urgent' => 'danger',
    ];
@endphp

<x-filament::section>
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ \App\Filament\Resources\TicketThreadResource::getUrl('index') }}"
               class="fi-icon-btn inline-flex items-center justify-center rounded-lg bg-white dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-primary-500/50 transition-colors"
               wire:navigate>
                <x-heroicon-o-arrow-left class="w-4 h-4" />
            </a>
            <div class="w-px h-6 bg-gray-200 dark:bg-white/10"></div>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                        {{ $ticket->subject }}
                    </h1>
                    <x-filament::badge :color="$statusColors[$ticket->status] ?? 'gray'">
                        {{ $ticket->status }}
                    </x-filament::badge>
                    <x-filament::badge :color="$priorityColors[$ticket->priority] ?? 'gray'">
                        {{ $ticket->priority }}
                    </x-filament::badge>
                </div>
                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                    <span class="text-[11px] text-gray-400 dark:text-gray-500 font-mono">{{ $ticket->number }}</span>
                    <span class="text-gray-300 dark:text-white/10">&middot;</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $ticket->user->name ?? '-' }}</span>
                    @if($ticket->department)
                        <span class="text-gray-300 dark:text-white/10">&middot;</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $ticket->department->name }}</span>
                    @endif
                    @if($ticket->service?->product)
                        <span class="text-gray-300 dark:text-white/10">&middot;</span>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $ticket->service->product->name }}</span>
                    @endif
                </div>
            </div>
        </div>
        @if($ticket->status !== 'closed')
            <button type="button" wire:click="updateStatus('closed')"
                class="fi-icon-btn inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors flex-shrink-0">
                <x-heroicon-o-check-circle class="w-3.5 h-3.5" /> Close
            </button>
        @endif
    </div>
</x-filament::section>
