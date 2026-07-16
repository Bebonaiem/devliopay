@extends('layouts.client')

@section('title', 'Support Tickets')

@section('content')
<div class="space-y-6" x-data="{ search: '' }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Support Tickets</h1>
            <p class="text-sm text-gray-400 mt-1">Get help from our support team.</p>
        </div>
        <a href="{{ route('client.tickets.create') }}" class="btn-primary px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> New Ticket
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @php
            $allCount = $tickets->count();
            $openCount = $tickets->where('status', 'open')->count();
            $answeredCount = $tickets->where('status', 'answered')->count();
            $closedCount = $tickets->where('status', 'closed')->count();
        @endphp
        <a href="{{ route('client.tickets.index') }}" class="glass rounded-xl p-4 hover:border-brand-500/20 transition-all text-center group">
            <div class="text-2xl font-bold text-white group-hover:text-brand-400 transition-colors">{{ $allCount }}</div>
            <div class="text-[11px] text-gray-500 font-medium mt-1">All Tickets</div>
        </a>
        <a href="{{ route('client.tickets.index', ['status' => 'open']) }}" class="glass rounded-xl p-4 hover:border-emerald-500/20 transition-all text-center group {{ request('status') === 'open' ? 'border-emerald-500/30 bg-emerald-500/5' : '' }}">
            <div class="text-2xl font-bold text-emerald-400">{{ $openCount }}</div>
            <div class="text-[11px] text-gray-500 font-medium mt-1">Open</div>
        </a>
        <a href="{{ route('client.tickets.index', ['status' => 'answered']) }}" class="glass rounded-xl p-4 hover:border-amber-500/20 transition-all text-center group {{ request('status') === 'answered' ? 'border-amber-500/30 bg-amber-500/5' : '' }}">
            <div class="text-2xl font-bold text-amber-400">{{ $answeredCount }}</div>
            <div class="text-[11px] text-gray-500 font-medium mt-1">Answered</div>
        </a>
        <a href="{{ route('client.tickets.index', ['status' => 'closed']) }}" class="glass rounded-xl p-4 hover:border-gray-500/20 transition-all text-center group {{ request('status') === 'closed' ? 'border-gray-500/30 bg-gray-500/5' : '' }}">
            <div class="text-2xl font-bold text-gray-400">{{ $closedCount }}</div>
            <div class="text-[11px] text-gray-500 font-medium mt-1">Closed</div>
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('client.tickets.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ !request('status') ? 'bg-brand-500/15 text-brand-400 border border-brand-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">All</a>
        <a href="{{ route('client.tickets.index', ['status' => 'open']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'open' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>Open
        </a>
        <a href="{{ route('client.tickets.index', ['status' => 'answered']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'answered' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5"></span>Answered
        </a>
        <a href="{{ route('client.tickets.index', ['status' => 'closed']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'closed' ? 'bg-gray-500/15 text-gray-400 border border-gray-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>Closed
        </a>
        <div class="flex-1"></div>
        <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" x-model="search" placeholder="Search tickets..." class="w-48 sm:w-64 bg-white/[0.03] border border-white/10 rounded-xl pl-9 pr-4 py-2 text-xs text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20 transition-all">
        </div>
    </div>

    {{-- Tickets --}}
    @if(isset($tickets) && count($tickets) > 0)
        <div class="space-y-2">
            @foreach($tickets as $ticket)
            <a href="{{ route('client.tickets.show', $ticket->id) }}" class="glass rounded-xl p-4 hover:border-brand-500/20 transition-all block group"
                x-show="search === '' || '{{ strtolower($ticket->subject) }}'.includes(search.toLowerCase()) || '{{ strtolower($ticket->number ?? '') }}'.includes(search.toLowerCase())">
                <div class="flex items-center gap-4">
                    {{-- Status indicator --}}
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                        {{ ($ticket->status === 'open') ? 'bg-emerald-500/10' : (($ticket->status === 'answered') ? 'bg-amber-500/10' : 'bg-gray-500/10') }}">
                        @if($ticket->status === 'open')
                            <i data-lucide="message-circle" class="w-5 h-5 text-emerald-400"></i>
                        @elseif($ticket->status === 'answered')
                            <i data-lucide="message-circle-more" class="w-5 h-5 text-amber-400"></i>
                        @else
                            <i data-lucide="message-circle-check" class="w-5 h-5 text-gray-400"></i>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="text-sm font-semibold group-hover:text-brand-400 transition-colors truncate">{{ $ticket->subject }}</h3>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider flex-shrink-0
                                {{ $ticket->status === 'open' ? 'bg-emerald-500/10 text-emerald-400' : '' }}
                                {{ $ticket->status === 'answered' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                {{ $ticket->status === 'closed' ? 'bg-gray-500/10 text-gray-400' : '' }}">{{ ucfirst($ticket->status) }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-gray-500">
                            <span class="font-mono">{{ $ticket->number }}</span>
                            @if($ticket->department)
                                <span class="inline-flex items-center gap-1"><i data-lucide="folder" class="w-3 h-3"></i>{{ $ticket->department->name }}</span>
                            @endif
                            <span class="inline-flex items-center gap-1"><i data-lucide="message-square" class="w-3 h-3"></i>{{ $ticket->messages_count ?? 0 }}</span>
                            <span>{{ $ticket->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    {{-- Priority & Arrow --}}
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($ticket->priority === 'urgent' || $ticket->priority === 'high')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $ticket->priority === 'urgent' ? 'bg-red-500/10 text-red-400' : 'bg-orange-500/10 text-orange-400' }}">{{ $ticket->priority }}</span>
                        @endif
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-600 group-hover:text-brand-400 transition-colors"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-20 text-center">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-500/10 to-purple-500/10 flex items-center justify-center mx-auto mb-5 border border-brand-500/10">
                <i data-lucide="life-buoy" class="w-10 h-10 text-brand-400/60"></i>
            </div>
            <h3 class="text-lg font-bold mb-2">{{ request('status') ? 'No ' . ucfirst(request('status')) . ' tickets' : 'No tickets yet' }}</h3>
            <p class="text-sm text-gray-400 mb-8 max-w-sm mx-auto">
                @if(request('status'))
                    No tickets match this filter.
                @else
                    You haven't opened any support tickets yet. Our team is here to help!
                @endif
            </p>
            <a href="{{ route('client.tickets.create') }}" class="btn-primary px-6 py-3 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Open Your First Ticket
            </a>
        </div>
    @endif
</div>
@endsection
