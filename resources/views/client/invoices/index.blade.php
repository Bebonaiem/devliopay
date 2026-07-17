@extends('layouts.client')

@section('title', 'Invoices')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Invoices</h1>
        <p class="text-sm text-gray-400 mt-1">View and manage your billing history.</p>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('client.invoices.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ !request('status') ? 'bg-brand-500/15 text-brand-400 border border-brand-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">All</a>
        <a href="{{ route('client.invoices.index', ['status' => 'paid']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'paid' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">Paid</a>
        <a href="{{ route('client.invoices.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'pending' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">Unpaid</a>
        <a href="{{ route('client.invoices.index', ['status' => 'overdue']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'overdue' ? 'bg-red-500/15 text-red-400 border border-red-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">Overdue</a>
    </div>

    {{-- Invoices --}}
    @if(isset($invoices) && count($invoices) > 0)
        <div class="grid grid-cols-1 gap-3">
            @foreach($invoices as $invoice)
            @php $invSymbol = $invoice->currency->symbol ?? $defaultCurrencySymbol; @endphp
            <a href="{{ route('client.invoices.show', $invoice->id) }}" class="block glass rounded-2xl p-5 hover:border-brand-500/20 transition-all group cursor-pointer">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-brand-500/20 transition-colors">
                        <i data-lucide="file-text" class="w-6 h-6 text-brand-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-sm font-semibold">#{{ $invoice->number ?? $invoice->id }}</h3>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                                {{ ($invoice->status ?? '') === 'paid' ? 'bg-emerald-500/10 text-emerald-400' : '' }}
                                {{ ($invoice->status ?? '') === 'pending' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                {{ ($invoice->status ?? '') === 'overdue' ? 'bg-red-500/10 text-red-400' : '' }}
                                {{ !in_array($invoice->status ?? '', ['paid','pending','overdue']) ? 'bg-gray-500/10 text-gray-400' : '' }}
                            ">{{ $invoice->status ?? 'unknown' }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                            <span><i data-lucide="calendar" class="w-3 h-3 inline mr-1"></i>{{ $invoice->created_at ? $invoice->created_at->format('M j, Y') : '-' }}</span>
                            @if($invoice->due_at)<span><i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>Due {{ $invoice->due_at->format('M j, Y') }}</span>@endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold">{{ $invSymbol }}{{ number_format($invoice->total ?? 0, 2) }}</span>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-600 group-hover:text-brand-400 transition-colors flex-shrink-0"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="file-text" class="w-8 h-8 text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">No invoices found</h3>
            <p class="text-sm text-gray-400">Your invoices will appear here once you make a purchase.</p>
        </div>
    @endif
</div>
@endsection
