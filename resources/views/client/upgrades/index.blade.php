@extends('layouts.client')

@section('title', 'Service Upgrades')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Service Upgrades</h1>
        <p class="text-sm text-gray-400 mt-1">Upgrade your existing services to a better plan.</p>
    </div>

    @if(isset($upgrades) && count($upgrades) > 0)
        <div class="grid grid-cols-1 gap-4">
            @foreach($upgrades as $upgrade)
            <div class="glass rounded-2xl p-6 hover:border-brand-500/20 transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="arrow-up-circle" class="w-6 h-6 text-brand-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold mb-1">{{ $upgrade->name ?? 'Service' }}</h3>
                        <p class="text-xs text-gray-500">{{ $upgrade->current_plan ?? '' }} &rarr; {{ $upgrade->new_plan ?? '' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-emerald-400">+${{ number_format($upgrade->price_diff ?? 0, 2) }}/mo</span>
                        <a href="{{ route('client.upgrades.pay', $upgrade->id) }}" class="btn-primary px-4 py-2 rounded-xl text-xs font-semibold text-white">Upgrade</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="arrow-up-circle" class="w-8 h-8 text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">No upgrades available</h3>
            <p class="text-sm text-gray-400">You don't have any services eligible for upgrade.</p>
        </div>
    @endif
</div>
@endsection
