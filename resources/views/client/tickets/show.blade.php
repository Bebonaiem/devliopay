@extends('layouts.client')

@section('title', 'Ticket #' . ($ticket->number ?? $ticket->id))

@section('content')
<div class="space-y-0" x-data="{ replyBox: false, replyMessage: '', sending: false }">
    {{-- Header Card --}}
    <div class="glass rounded-2xl p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                    {{ ($ticket->status === 'open') ? 'bg-emerald-500/10' : (($ticket->status === 'answered') ? 'bg-amber-500/10' : 'bg-gray-500/10') }}">
                    @if($ticket->status === 'open')
                        <i data-lucide="message-circle" class="w-6 h-6 text-emerald-400"></i>
                    @elseif($ticket->status === 'answered')
                        <i data-lucide="message-circle-more" class="w-6 h-6 text-amber-400"></i>
                    @else
                        <i data-lucide="message-circle-check" class="w-6 h-6 text-gray-400"></i>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <h1 class="text-xl font-bold tracking-tight">{{ $ticket->subject }}</h1>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                            {{ $ticket->status === 'open' ? 'bg-emerald-500/10 text-emerald-400' : '' }}
                            {{ $ticket->status === 'answered' ? 'bg-amber-500/10 text-amber-400' : '' }}
                            {{ $ticket->status === 'closed' ? 'bg-gray-500/10 text-gray-400' : '' }}">{{ ucfirst($ticket->status) }}</span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                            {{ $ticket->priority === 'urgent' ? 'bg-red-500/10 text-red-400' : '' }}
                            {{ $ticket->priority === 'high' ? 'bg-orange-500/10 text-orange-400' : '' }}
                            {{ $ticket->priority === 'medium' ? 'bg-blue-500/10 text-blue-400' : '' }}
                            {{ $ticket->priority === 'low' ? 'bg-gray-500/10 text-gray-400' : '' }}">{{ ucfirst($ticket->priority) }}</span>
                        <span class="text-[11px] text-gray-500 font-mono">{{ $ticket->number }}</span>
                        @if($ticket->department)
                            <span class="text-[11px] text-gray-500"><i data-lucide="folder" class="w-3 h-3 inline"></i> {{ $ticket->department->name }}</span>
                        @endif
                        <span class="text-[11px] text-gray-500"><i data-lucide="clock" class="w-3 h-3 inline"></i> {{ $ticket->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                @if($ticket->status !== 'closed')
                    <button @click="replyBox = !replyBox; if(replyBox) $nextTick(() => $refs.replyInput?.focus())" class="btn-primary px-4 py-2 rounded-xl text-xs font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
                        <i data-lucide="reply" class="w-3.5 h-3.5"></i> Reply
                    </button>
                    <form id="close-ticket-form" method="POST" action="{{ route('client.tickets.close', $ticket->id) }}">
                        @csrf
                        <button type="button" onclick="showConfirm({title: 'Close Ticket', message: 'Are you sure you want to close this ticket? You will not be able to reply further.', type: 'warning', confirmText: 'Close Ticket', callback: 'close-ticket-form'})" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-400 bg-white/5 hover:bg-white/10 border border-white/10 transition-all inline-flex items-center gap-2">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Close
                        </button>
                    </form>
                @else
                    <form id="reopen-ticket-form" method="POST" action="{{ route('client.tickets.reopen', $ticket->id) }}">
                        @csrf
                        <button type="button" onclick="showConfirm({title: 'Reopen Ticket', message: 'Are you sure you want to reopen this ticket?', type: 'info', confirmText: 'Reopen', callback: 'reopen-ticket-form'})" class="px-4 py-2 rounded-xl text-xs font-semibold text-brand-400 bg-brand-500/5 hover:bg-brand-500/10 border border-brand-500/10 transition-all inline-flex items-center gap-2">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Reopen
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Conversation --}}
    <div class="space-y-4 mb-6">
        @foreach($ticket->messages as $message)
        <div class="flex {{ $message->is_staff ? 'justify-start' : 'justify-end' }}">
            <div class="max-w-[85%] sm:max-w-[70%]">
                {{-- Bubble --}}
                <div class="rounded-2xl px-5 py-4 {{ $message->is_staff ? 'bg-white/[0.03] border border-white/5 rounded-tl-md' : 'bg-brand-500/10 border border-brand-500/10 rounded-tr-md' }}">
                    {{-- Author --}}
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded-lg {{ $message->is_staff ? 'bg-brand-500/20' : 'bg-white/10' }} flex items-center justify-center flex-shrink-0">
                            @if($message->is_staff)
                                <i data-lucide="headphones" class="w-3 h-3 text-brand-400"></i>
                            @else
                                <span class="text-[10px] font-bold text-gray-400">{{ substr($message->user->name ?? 'U', 0, 1) }}</span>
                            @endif
                        </div>
                        <span class="text-xs font-semibold {{ $message->is_staff ? 'text-brand-400' : 'text-gray-300' }}">{{ $message->is_staff ? 'Support' : ($message->user->name ?? 'You') }}</span>
                        @if($message->is_staff)
                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-brand-500/10 text-brand-400 uppercase">Staff</span>
                        @endif
                        <span class="text-[10px] text-gray-500 ml-auto">{{ $message->created_at->diffForHumans() }}</span>
                    </div>
                    {{-- Message --}}
                    <div class="text-sm text-gray-300 leading-relaxed prose prose-invert prose-sm max-w-none">{!! $message->message !!}</div>
                </div>

                {{-- Attachments --}}
                @if(isset($message->attachments) && $message->attachments->count() > 0)
                <div class="mt-2 flex flex-wrap gap-1.5 {{ $message->is_staff ? '' : 'justify-end' }}">
                    @foreach($message->attachments as $attachment)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($attachment->path) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $message->is_staff ? 'bg-white/5 hover:bg-white/10 border border-white/5' : 'bg-brand-500/5 hover:bg-brand-500/10 border border-brand-500/10' }} transition-all text-xs text-gray-300 group/att">
                        <i data-lucide="paperclip" class="w-3 h-3 {{ $message->is_staff ? 'text-gray-500' : 'text-brand-400/60' }}"></i>
                        <span class="group-hover/att:text-white transition-colors">{{ $attachment->filename }}</span>
                        @if($attachment->size)
                            <span class="text-gray-500 text-[10px]">{{ round($attachment->size / 1024, 1) }}KB</span>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Reply Form --}}
    @if($ticket->status !== 'closed')
    <div x-show="replyBox" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="glass rounded-2xl p-5">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center">
                <i data-lucide="reply" class="w-4 h-4 text-brand-400"></i>
            </div>
            <h2 class="text-sm font-semibold">Reply to Ticket</h2>
        </div>
        @error('message')
            <div class="mb-4 p-3 rounded-xl bg-red-500/5 border border-red-500/10 text-sm text-red-400">{{ $message }}</div>
        @enderror
        <form method="POST" action="{{ route('client.tickets.reply', $ticket->id) }}" enctype="multipart/form-data" x-on:submit="sending = true">
            @csrf
            <div class="mb-3">
                <textarea name="message" x-ref="replyInput" x-model="replyMessage" rows="4" required
                    class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20 resize-none transition-all"
                    placeholder="Type your reply..."></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-400 mb-2">Attachments (optional)</label>
                <input type="file" name="attachments[]" multiple
                    class="w-full text-xs text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 file:cursor-pointer"
                    accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.zip">
                <p class="text-[10px] text-gray-500 mt-1">Max 5 files, 10MB each</p>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="replyBox = false" class="px-4 py-2 rounded-xl text-xs font-medium text-gray-400 hover:text-gray-300 transition-all">
                    Cancel
                </button>
                <button type="submit" :disabled="sending" class="btn-primary px-5 py-2 rounded-xl text-xs font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2 disabled:opacity-50">
                    <template x-if="!sending">
                        <span class="inline-flex items-center gap-2"><i data-lucide="send" class="w-3.5 h-3.5"></i> Send Reply</span>
                    </template>
                    <template x-if="sending">
                        <span class="inline-flex items-center gap-2"><svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Sending...</span>
                    </template>
                </button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
