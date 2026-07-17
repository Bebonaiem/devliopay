@extends('layouts.client')

@section('title', 'Upgrade Payment')

@php $symbol = $service->pricing->currencies->first()?->symbol ?? $defaultCurrencySymbol; @endphp

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('client.upgrades.index') }}" class="hover:text-gray-300 transition-colors">Upgrades</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-300">Payment</span>
    </div>

    <div>
        <h1 class="text-2xl font-bold tracking-tight">Upgrade Payment</h1>
        <p class="text-sm text-gray-400 mt-1">Complete your service upgrade.</p>
    </div>

    <div class="glass rounded-2xl p-6">
        <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5 mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-400">Service</span>
                <span class="text-sm font-medium">{{ $upgrade->name ?? 'Service' }}</span>
            </div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-400">Plan Change</span>
                <span class="text-sm font-medium">{{ $upgrade->current_plan ?? '' }} &rarr; {{ $upgrade->new_plan ?? '' }}</span>
            </div>
            <hr class="border-white/5 my-3">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold">Amount Due</span>
                <span class="text-lg font-black">{{ $symbol }}{{ number_format($upgrade->price_diff ?? 0, 2) }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('client.upgrades.process', $upgrade->id) }}">
            @csrf
            <div class="space-y-3 mb-6">
                <label class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.02] border border-white/10 hover:border-brand-500/30 cursor-pointer transition-all has-[:checked]:border-brand-500/50 has-[:checked]:bg-brand-500/5">
                    <input type="radio" name="payment_method" value="balance" checked class="sr-only peer">
                    <div class="w-5 h-5 rounded-full border-2 border-gray-600 peer-checked:border-brand-500 flex items-center justify-center"><div class="w-2.5 h-2.5 rounded-full bg-brand-500 scale-0 peer-checked:scale-100 transition-transform"></div></div>
                    <i data-lucide="wallet" class="w-5 h-5 text-gray-400 peer-checked:text-brand-400"></i>
                    <div>
                        <p class="text-sm font-medium">Credit Balance</p>
                        <p class="text-xs text-gray-500">Available: {{ $symbol }}{{ number_format($balance ?? 0, 2) }}</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.02] border border-white/10 hover:border-brand-500/30 cursor-pointer transition-all has-[:checked]:border-brand-500/50 has-[:checked]:bg-brand-500/5">
                    <input type="radio" name="payment_method" value="stripe" class="sr-only peer">
                    <div class="w-5 h-5 rounded-full border-2 border-gray-600 peer-checked:border-brand-500 flex items-center justify-center"><div class="w-2.5 h-2.5 rounded-full bg-brand-500 scale-0 peer-checked:scale-100 transition-transform"></div></div>
                    <i data-lucide="credit-card" class="w-5 h-5 text-gray-400 peer-checked:text-brand-400"></i>
                    <div>
                        <p class="text-sm font-medium">Credit Card</p>
                        <p class="text-xs text-gray-500">Pay securely with Stripe</p>
                    </div>
                </label>
            </div>

            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20">
                <i data-lucide="check-circle" class="w-4 h-4 inline mr-2"></i> Complete Upgrade
            </button>
        </form>
    </div>
</div>
@endsection
