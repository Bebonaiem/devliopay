@extends('layouts.client')

@section('title', 'Notifications')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Notifications</h1>
            <p class="text-sm text-gray-400 mt-1">Manage your notification preferences and view history.</p>
        </div>
        <div class="flex items-center gap-2">
            @if($notifications->isNotEmpty())
            <button onclick="markAllRead()" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-400 hover:text-white border border-white/5 hover:border-white/10 transition-colors">
                Mark all read
            </button>
            <form method="POST" action="{{ route('client.notifications.clear') }}" onsubmit="return confirm('Clear all notification history?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-red-400 hover:text-red-300 border border-red-500/20 hover:border-red-500/40 transition-colors">
                    Clear all
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'history' }">
        <div class="flex gap-1 p-1 glass rounded-xl w-fit">
            <button @click="tab = 'history'" :class="tab === 'history' ? 'bg-brand-500/15 text-brand-400' : 'text-gray-400 hover:text-white'" class="px-5 py-2 rounded-lg text-xs font-semibold transition-all">
                History
                @if($notifications->where('read_at', null)->count() > 0)
                <span class="ml-1.5 w-5 h-5 bg-brand-500 text-[10px] font-bold text-white rounded-full inline-flex items-center justify-center">{{ $notifications->where('read_at', null)->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'preferences'" :class="tab === 'preferences' ? 'bg-brand-500/15 text-brand-400' : 'text-gray-400 hover:text-white'" class="px-5 py-2 rounded-lg text-xs font-semibold transition-all">
                Preferences
            </button>
        </div>

        {{-- History --}}
        <div x-show="tab === 'history'" x-transition class="mt-4">
            @if($notifications->count() > 0)
                <div class="space-y-2">
                    @foreach($notifications as $notification)
                    <div class="glass rounded-2xl p-5 {{ $notification->read_at ? 'opacity-60' : '' }}" id="notification-{{ $notification->id }}">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl {{ $notification->read_at ? 'bg-white/5' : 'bg-brand-500/10' }} flex items-center justify-center flex-shrink-0 mt-0.5">
                                @php
                                    $type = $notification->type ?? '';
                                    $icon = 'bell';
                                    if (str_contains($type, 'Invoice')) $icon = 'file-text';
                                    elseif (str_contains($type, 'Payment')) $icon = 'credit-card';
                                    elseif (str_contains($type, 'Service')) $icon = 'server';
                                    elseif (str_contains($type, 'Ticket')) $icon = 'life-buoy';
                                @endphp
                                <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $notification->read_at ? 'text-gray-500' : 'text-brand-400' }}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                                <div class="flex items-center gap-3 mt-2">
                                    <p class="text-[11px] text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                                    @if(!empty($notification->data['url']))
                                    <a href="{{ $notification->data['url'] }}" class="text-[11px] text-brand-400 hover:text-brand-300 font-medium">View</a>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @unless($notification->read_at)
                                <button onclick="markAsRead('{{ $notification->id }}')" class="w-2 h-2 rounded-full bg-brand-500 flex-shrink-0 mt-2 hover:bg-brand-400 transition-colors" title="Mark as read"></button>
                                @endunless
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="glass rounded-2xl px-6 py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="bell" class="w-8 h-8 text-gray-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">No notifications</h3>
                    <p class="text-sm text-gray-400">You're all caught up!</p>
                </div>
            @endif
        </div>

        {{-- Preferences --}}
        <div x-show="tab === 'preferences'" x-transition class="mt-4">
            <form method="POST" action="{{ route('client.notifications.update') }}">
                @csrf
                @method('PUT')
                <div class="glass rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-white/5">
                                    <th class="text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Notification</th>
                                    <th class="text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Email</th>
                                    <th class="text-center text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Dashboard</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @foreach($preferences as $type => $pref)
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium">{{ $pref['label'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="preferences[{{ $type }}][email]" value="1" {{ $pref['email'] ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-500"></div>
                                        </label>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="preferences[{{ $type }}][dashboard]" value="1" {{ $pref['dashboard'] ? 'checked' : '' }} class="sr-only peer">
                                            <div class="w-9 h-5 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-500"></div>
                                        </label>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-xs font-semibold text-white shadow-lg shadow-brand-500/20">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function markAsRead(id) {
        fetch('{{ route("client.notifications.mark-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id: id })
        }).then(() => {
            const el = document.getElementById('notification-' + id);
            if (el) {
                el.classList.add('opacity-60');
                const dot = el.querySelector('.bg-brand-500');
                if (dot) dot.remove();
            }
        });
    }

    function markAllRead() {
        fetch('{{ route("client.notifications.mark-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => location.reload());
    }
</script>
@endpush
@endsection
