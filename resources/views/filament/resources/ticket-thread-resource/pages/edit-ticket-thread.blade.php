<x-filament-panels::page>
    @include('filament.resources.ticket-thread-resource.pages.partials.ticket-header')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content: Form + Chat --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Filament Form --}}
            <form wire:submit="save">
                {{ $this->form }}

                <div class="mt-6 flex items-center gap-3">
                    <x-filament::button type="submit" wire:loading.attr="disabled">
                        Save Changes
                    </x-filament::button>

                    <x-filament::button tag="a" href="{{ \App\Filament\Resources\TicketThreadResource::getUrl('index') }}" color="gray" wire:loading.attr="disabled">
                        Cancel
                    </x-filament::button>
                </div>
            </form>

            {{-- Chat Messages --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-gray-400" />
                        <span>Conversation</span>
                        <x-filament::badge>{{ count($messages) }}</x-filament::badge>
                    </div>
                </x-slot>

                <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2" id="ticket-messages">
                    @forelse($messages as $message)
                        @php
                            $isStaff = $message->is_staff;
                            $isCurrentUser = $message->user_id === auth()->id();
                        @endphp
                        <div class="flex {{ $isStaff ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] {{ $isStaff
                                ? 'bg-primary-100 dark:bg-primary-800 border border-primary-300 dark:border-primary-600'
                                : 'bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600'
                            }} rounded-xl p-4">
                                {{-- Message Header --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex items-center justify-center w-6 h-6 rounded-full {{ $isStaff ? 'bg-primary-100 dark:bg-primary-800' : 'bg-gray-300 dark:bg-gray-600' }}">
                                        @if($isStaff)
                                            <x-heroicon-s-shield-check class="w-3.5 h-3.5 text-primary-600 dark:text-primary-300" />
                                        @else
                                            <x-heroicon-s-user class="w-3.5 h-3.5 text-gray-600 dark:text-gray-300" />
                                        @endif
                                    </div>
                                    <span class="text-xs font-semibold {{ $isStaff ? 'text-primary-800 dark:text-white' : 'text-gray-900 dark:text-white' }}">
                                        {{ $message->user->name ?? 'Unknown' }}
                                    </span>
                                    @if($isStaff)
                                        <x-filament::badge color="primary" size="xs">Staff</x-filament::badge>
                                    @endif
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 ml-auto">
                                        {{ $message->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                {{-- Message Body --}}
                                <div class="text-sm leading-relaxed prose prose-sm dark:prose-invert max-w-none {{ $isStaff ? 'text-primary-900 dark:text-white' : 'text-gray-800 dark:text-white' }}">
                                    {!! $message->message !!}
                                </div>

                                {{-- Attachments --}}
                                @if($message->attachments && $message->attachments->count())
                                    <div class="mt-3 pt-3 border-t {{ $isStaff ? 'border-primary-300 dark:border-primary-600' : 'border-gray-300 dark:border-gray-600' }}">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($message->attachments as $attachment)
                                                <a href="{{ Storage::url($attachment->path) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $isStaff ? 'bg-primary-200 dark:bg-primary-700 hover:bg-primary-300 dark:hover:bg-primary-600 text-primary-800 dark:text-white' : 'bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-800 dark:text-white' }} text-xs transition-colors">
                                                    <x-heroicon-o-paper-clip class="w-3 h-3" />
                                                    {{ $attachment->filename }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" />
                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No messages yet.</p>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Quick Info --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-gray-400" />
                        <span>Ticket Info</span>
                    </div>
                </x-slot>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $ticket->created_at->diffForHumans() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Last Updated</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $ticket->updated_at->diffForHumans() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Customer</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $ticket->user->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Department</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $ticket->department->name ?? 'Not assigned' }}</dd>
                    </div>
                    @if($ticket->service?->product)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Related Service</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $ticket->service->product->name }}</dd>
                        </div>
                    @endif
                </dl>
            </x-filament::section>
        </div>
    </div>

    @script
    <script>
        $wire.on('messageSent', () => {
            const container = document.getElementById('ticket-messages');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });

        // Auto-scroll on load
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('ticket-messages');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    </script>
    @endscript
</x-filament-panels::page>
