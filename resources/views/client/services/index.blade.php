@extends('layouts.client')

@section('title', 'My Services')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">My Services</h1>
            <p class="text-sm text-gray-400 mt-1">Manage your active hosting services.</p>
        </div>
        <a href="{{ route('store.index') }}" class="btn-primary px-5 py-2.5 rounded-xl text-xs font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Order New
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('client.services.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ !request('status') ? 'bg-brand-500/15 text-brand-400 border border-brand-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">All</a>
        <a href="{{ route('client.services.index', ['status' => 'active']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'active' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">Active</a>
        <a href="{{ route('client.services.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'pending' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">Pending</a>
        <a href="{{ route('client.services.index', ['status' => 'suspended']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'suspended' ? 'bg-red-500/15 text-red-400 border border-red-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">Suspended</a>
        <a href="{{ route('client.services.index', ['status' => 'cancelled']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'cancelled' ? 'bg-gray-500/15 text-gray-400 border border-gray-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">Cancelled</a>
        <a href="{{ route('client.services.index', ['status' => 'terminated']) }}" class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ request('status') === 'terminated' ? 'bg-red-500/15 text-red-400 border border-red-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">Terminated</a>
    </div>

    {{-- Services --}}
    @if(isset($services) && count($services) > 0)
        <div class="grid grid-cols-1 gap-4">
            @foreach($services as $service)
            <a href="{{ route('client.services.show', $service->id) }}" class="block glass rounded-2xl p-6 hover:border-brand-500/20 transition-all group cursor-pointer">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-brand-500/20 transition-colors">
                        <i data-lucide="server" class="w-6 h-6 text-brand-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-sm font-semibold">{{ $service->name ?? $service->product->name ?? 'Service' }}</h3>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                                {{ ($service->status ?? '') === 'active' ? 'bg-emerald-500/10 text-emerald-400' : '' }}
                                {{ ($service->status ?? '') === 'pending' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                {{ ($service->status ?? '') === 'suspended' ? 'bg-red-500/10 text-red-400' : '' }}
                                {{ !in_array($service->status ?? '', ['active','pending','suspended']) ? 'bg-gray-500/10 text-gray-400' : '' }}
                            ">{{ $service->status ?? 'unknown' }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                            @if($service->billing_cycle)<span><i data-lucide="calendar" class="w-3 h-3 inline mr-1"></i>{{ ucfirst($service->billing_cycle) }}</span>@endif
                            @if($service->next_billing_date)<span><i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>Renews {{ $service->next_billing_date->format('M j, Y') }}</span>@endif
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-5 h-5 text-gray-600 group-hover:text-brand-400 transition-colors flex-shrink-0"></i>
                </div>
            </a>
            @endforeach
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="server" class="w-8 h-8 text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">No services found</h3>
            <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">You don't have any services yet. Browse our store to find the perfect plan for you.</p>
            <a href="{{ route('store.index') }}" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Browse Store
            </a>
        </div>
    @endif
</div>
@endsection
