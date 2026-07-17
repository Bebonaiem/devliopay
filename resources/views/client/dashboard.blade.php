@extends('layouts.client')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Welcome --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Welcome back, {{ explode(' ', auth()->user()->name)[0] ?? 'there' }}</h1>
            <p class="text-sm text-gray-400 mt-1">Here's what's happening with your services.</p>
        </div>
        <a href="{{ route('store.index') }}" class="btn-primary px-5 py-2.5 rounded-xl text-xs font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2 w-fit">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> New Service
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-brand-500/10 flex items-center justify-center"><i data-lucide="server" class="w-5 h-5 text-brand-400"></i></div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Services</span>
            </div>
            <div class="text-2xl font-black">{{ $activeServices ?? 0 }}</div>
            <p class="text-xs text-gray-500 mt-1">Active services</p>
        </div>
        <div class="stat-card rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center"><i data-lucide="file-text" class="w-5 h-5 text-emerald-400"></i></div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Invoices</span>
            </div>
            <div class="text-2xl font-black">{{ $pendingInvoices ?? 0 }}</div>
            <p class="text-xs text-gray-500 mt-1">Pending invoices</p>
        </div>
        <div class="stat-card rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center"><i data-lucide="wallet" class="w-5 h-5 text-amber-400"></i></div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Spent</span>
            </div>
            <div class="text-2xl font-black">{{ $defaultCurrencySymbol }}{{ number_format((float) ($totalSpent ?? 0), 2) }}</div>
            <p class="text-xs text-gray-500 mt-1">Total spent</p>
        </div>
        <div class="stat-card rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-pink-500/10 flex items-center justify-center"><i data-lucide="life-buoy" class="w-5 h-5 text-pink-400"></i></div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Tickets</span>
            </div>
            <div class="text-2xl font-black">{{ $openTickets ?? 0 }}</div>
            <p class="text-xs text-gray-500 mt-1">Open tickets</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Services --}}
        <div class="lg:col-span-2 glass rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                <h2 class="text-sm font-semibold">Recent Services</h2>
                <a href="{{ route('client.services.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-medium">View All</a>
            </div>
            @if(isset($recentServices) && count($recentServices) > 0)
                <div class="divide-y divide-white/5">
                    @foreach($recentServices as $service)
                    <a href="{{ route('client.services.show', $service->id) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-white/[0.02] transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-brand-500/10 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="server" class="w-5 h-5 text-brand-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ $service->product->name ?? 'Service' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Renews {{ $service->next_billing_at ? $service->next_billing_at->format('M j, Y') : 'N/A' }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold
                            {{ $service->status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : '' }}
                            {{ $service->status === 'pending' ? 'bg-amber-500/10 text-amber-400' : '' }}
                            {{ $service->status === 'suspended' ? 'bg-red-500/10 text-red-400' : '' }}
                            {{ !in_array($service->status, ['active','pending','suspended']) ? 'bg-gray-500/10 text-gray-400' : '' }}
                        ">{{ ucfirst($service->status) }}</span>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <i data-lucide="server" class="w-10 h-10 text-gray-600 mx-auto mb-3"></i>
                    <p class="text-sm text-gray-400 mb-3">No services yet</p>
                    <a href="{{ route('store.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-medium">Browse the store to get started</a>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Spending Chart --}}
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold">Spending (12 months)</h2>
                </div>
                <div class="p-6">
                    @if(!empty($chartData))
                        @php
                            $maxVal = max(array_values($chartData));
                            $maxVal = $maxVal > 0 ? $maxVal : 1;
                        @endphp
                        <div class="flex items-end gap-1 h-32">
                            @foreach($chartData as $month => $amount)
                                <div class="flex-1 flex flex-col items-center gap-1">
                                    <div class="w-full rounded-t bg-brand-500/60 hover:bg-brand-400/80 transition-colors"
                                         style="height: {{ ($amount / $maxVal) * 100 }}%"
                                          title="{{ $month }}: {{ $defaultCurrencySymbol }}{{ number_format($amount, 2) }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-[10px] text-gray-600">{{ now()->subMonths(11)->format('M') }}</span>
                            <span class="text-[10px] text-gray-600">{{ now()->format('M') }}</span>
                        </div>
                    @else
                        <p class="text-xs text-gray-500 text-center py-4">No spending data yet</p>
                    @endif
                </div>
            </div>

            {{-- Service Breakdown --}}
            @if(!empty($serviceBreakdown))
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold">Services by Category</h2>
                </div>
                <div class="p-4 space-y-2">
                    @foreach($serviceBreakdown as $category => $count)
                    <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-white/[0.02]">
                        <span class="text-xs text-gray-300">{{ $category }}</span>
                        <span class="text-xs font-semibold text-brand-400">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Upcoming Renewals --}}
            @if(isset($upcomingRenewals) && count($upcomingRenewals) > 0)
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold">Upcoming Renewals</h2>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach($upcomingRenewals as $service)
                    <a href="{{ route('client.services.show', $service->id) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-white/[0.02] transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="clock" class="w-4 h-4 text-amber-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium truncate">{{ $service->product->name ?? 'Service' }}</p>
                            <p class="text-[11px] text-gray-500">{{ $service->next_billing_at->format('M j, Y') }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recent Invoices --}}
            @if(isset($recentInvoices) && count($recentInvoices) > 0)
            <div class="glass rounded-2xl overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold">Recent Invoices</h2>
                    <a href="{{ route('client.invoices.index') }}" class="text-xs text-brand-400 hover:text-brand-300 font-medium">View All</a>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach($recentInvoices as $invoice)
                    <a href="{{ route('client.invoices.show', $invoice->id) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-white/[0.02] transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium">{{ $invoice->number }}</p>
                            <p class="text-[11px] text-gray-500">{{ $invoice->created_at->format('M j, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold">{{ $defaultCurrencySymbol }}{{ number_format($invoice->total, 2) }}</p>
                            <span class="text-[10px] font-semibold
                                {{ $invoice->status === 'paid' ? 'text-emerald-400' : '' }}
                                {{ $invoice->status === 'pending' ? 'text-amber-400' : '' }}
                                {{ $invoice->status === 'overdue' ? 'text-red-400' : '' }}
                                {{ !in_array($invoice->status, ['paid','pending','overdue']) ? 'text-gray-400' : '' }}
                            ">{{ ucfirst($invoice->status) }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recent Activity --}}
            @if(isset($recentActivity) && count($recentActivity) > 0)
            <div class="glass rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/5">
                    <h2 class="text-sm font-semibold">Recent Activity</h2>
                </div>
                <div class="divide-y divide-white/5">
                    @foreach($recentActivity as $activity)
                    <div class="px-4 py-3">
                        <p class="text-xs text-gray-300">{{ $activity->product->name ?? 'Service' }}</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Updated {{ $activity->updated_at->diffForHumans() }} &middot; {{ ucfirst($activity->status) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
