@extends('layouts.app')

@section('title', $product->name ?? 'Product')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
        <a href="{{ route('store.index') }}" class="hover:text-gray-300 transition-colors">Store</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        @if($product->category ?? null)
        <a href="{{ route('store.index', ['category' => $product->category->slug]) }}" class="hover:text-gray-300 transition-colors">{{ $product->category->name }}</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        @endif
        <span class="text-gray-300">{{ $product->name }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        {{-- Product Info --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Image --}}
            <div class="glass rounded-2xl overflow-hidden h-64 sm:h-80">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-500/10 to-purple-500/10">
                        <i data-lucide="server" class="w-20 h-20 text-brand-400/20"></i>
                    </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-3">About This Product</h2>
                <div class="text-sm text-gray-400 leading-relaxed prose prose-invert prose-sm max-w-none">
                    {!! nl2br(e($product->description ?? 'No description available.')) !!}
                </div>
            </div>

            {{-- Features --}}
            @if($product->features ?? null)
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-3">Features</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($product->features as $feature)
                    <div class="flex items-center gap-2 text-sm text-gray-400">
                        <i data-lucide="check" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                        {{ $feature }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Order Form --}}
        <div class="lg:col-span-2">
            <div class="glass rounded-2xl p-6 sticky top-24 space-y-5">
                <div>
                    <h1 class="text-xl font-bold">{{ $product->name }}</h1>
                    <p class="text-sm text-gray-400 mt-1">{{ $product->category->name ?? 'Hosting' }}</p>
                </div>

                @php
                    $firstPlan = $product->pricing->first();
                    $firstPlanCurrency = $firstPlan?->currencies->first();
                    $firstPlanSymbol = $firstPlanCurrency?->symbol ?? $defaultCurrencySymbol;
                    $firstPlanAmount = $firstPlanCurrency?->pivot->amount ?? $product->base_price ?? 0;
                    $firstPlanSetupFee = $firstPlanCurrency?->pivot->setup_fee ?? 0;
                @endphp
                <div class="text-3xl font-black">
                    <span id="selected-price">{{ $firstPlanSymbol }}{{ number_format($firstPlanAmount, 2) }}</span>
                    <span class="text-sm font-medium text-gray-500">/mo</span>
                </div>

                <div id="setup-fee-line" class="flex items-center gap-2 text-xs {{ $firstPlanSetupFee > 0 ? '' : 'hidden' }}" style="{{ $firstPlanSetupFee > 0 ? '' : 'display:none' }}">
                    <i data-lucide="info" class="w-3 h-3 text-amber-400"></i>
                    <span class="text-gray-400">+ <span id="setup-fee-amount">{{ $firstPlanSymbol }}{{ number_format($firstPlanSetupFee, 2) }}</span> one-time setup fee</span>
                </div>

                <form method="POST" action="{{ route('cart.add') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    {{-- Billing Cycle --}}
                    @if(isset($product->pricing) && count($product->pricing) > 0)
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Billing Cycle</label>
                        <div class="space-y-2">
                            @foreach($product->pricing as $plan)
                            @php
                                $planCurrencies = $plan->currencies;
                                $planCurrency = $planCurrencies->first();
                                $planSymbol = $planCurrency?->symbol ?? $defaultCurrencySymbol;
                                $planAmount = $planCurrency?->pivot->amount ?? 0;
                                $planSetupFee = $planCurrency?->pivot->setup_fee ?? 0;
                                $currenciesJson = $planCurrencies->map(fn ($c) => [
                                    'id' => $c->id,
                                    'symbol' => $c->symbol,
                                    'code' => $c->code,
                                    'amount' => $c->pivot->amount,
                                    'setup_fee' => $c->pivot->setup_fee,
                                ])->values()->toJson();
                            @endphp
                            <label class="group block p-3 rounded-xl bg-white/[0.02] border border-white/10 hover:border-brand-500/30 cursor-pointer transition-all has-[:checked]:border-brand-500/50 has-[:checked]:bg-brand-500/5">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="pricing_id" value="{{ $plan->id }}" {{ $loop->first ? 'checked' : '' }} class="sr-only" onchange="updatePlan({{ $currenciesJson }}, this.value)">
                                    <div class="w-4 h-4 rounded-full border-2 border-gray-600 group-has-[:checked]:border-brand-500 flex items-center justify-center transition-colors">
                                        <div class="w-2 h-2 rounded-full bg-brand-500 scale-0 group-has-[:checked]:scale-100 transition-transform"></div>
                                    </div>
                                    <span class="text-sm font-medium flex-1">{{ $plan->name ?? $plan->cycle }}</span>
                                    <span class="text-sm font-semibold plan-price" data-plan-id="{{ $plan->id }}">{{ $planSymbol }}{{ number_format($planAmount, 2) }}<span class="text-gray-500 font-normal">{{ $plan->frequency }}</span></span>
                                </div>
                                @if(count($planCurrencies) > 1)
                                <div class="mt-2 ml-7 flex flex-wrap gap-1.5 currency-options" data-plan-id="{{ $plan->id }}">
                                    @foreach($planCurrencies as $c)
                                    <button type="button" onclick="selectCurrency(this, {{ $c->id }}, '{{ $c->symbol }}', '{{ $c->code }}', {{ $c->pivot->amount }}, {{ $c->pivot->setup_fee }})" data-currency-id="{{ $c->id }}" class="currency-btn px-2 py-0.5 rounded-md text-[10px] font-semibold border transition-all {{ $loop->first ? 'border-brand-500/50 bg-brand-500/10 text-brand-400' : 'border-white/10 text-gray-500 hover:text-gray-300 hover:border-white/20' }}">{{ $c->symbol }} {{ $c->code }}</button>
                                    @endforeach
                                </div>
                                @endif
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="currency_id" id="currency_id" value="{{ $firstPlanCurrency?->id }}">
                    @endif

                    {{-- Qty --}}
                    @if($product->allow_quantity !== 'no')
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Quantity</label>
                        <input type="number" name="quantity" value="1" min="1" max="10" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
                    </div>
                    @endif

                    <button type="submit" class="btn-primary w-full py-3.5 rounded-xl text-sm font-bold text-white shadow-xl shadow-brand-500/25 inline-flex items-center justify-center gap-2">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i> Add to Cart
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let currentCurrencies = [];

function updatePlan(currencies, planId) {
    currentCurrencies = currencies;
    if (currencies.length > 0) {
        const c = currencies[0];
        document.getElementById('selected-price').textContent = c.symbol + parseFloat(c.amount).toFixed(2);
        document.getElementById('currency_id').value = c.id;
        var sf = parseFloat(c.setup_fee) || 0;
        document.getElementById('setup-fee-line').style.display = sf > 0 ? 'flex' : 'none';
        document.getElementById('setup-fee-amount').textContent = c.symbol + sf.toFixed(2);
    }

    document.querySelectorAll('.currency-options').forEach(el => el.style.display = 'none');
    var activeOptions = document.querySelector('.currency-options[data-plan-id="' + planId + '"]');
    if (activeOptions) activeOptions.style.display = 'flex';
}

function selectCurrency(btn, currencyId, symbol, code, amount, setupFee) {
    document.getElementById('currency_id').value = currencyId;
    document.getElementById('selected-price').textContent = symbol + parseFloat(amount).toFixed(2);
    var sf = parseFloat(setupFee) || 0;
    document.getElementById('setup-fee-line').style.display = sf > 0 ? 'flex' : 'none';
    document.getElementById('setup-fee-amount').textContent = symbol + sf.toFixed(2);

    var parent = btn.closest('.currency-options');
    parent.querySelectorAll('.currency-btn').forEach(b => {
        b.classList.remove('border-brand-500/50', 'bg-brand-500/10', 'text-brand-400');
        b.classList.add('border-white/10', 'text-gray-500');
    });
    btn.classList.remove('border-white/10', 'text-gray-500');
    btn.classList.add('border-brand-500/50', 'bg-brand-500/10', 'text-brand-400');
}

document.addEventListener('DOMContentLoaded', function() {
    var firstPlanBtn = document.querySelector('input[name="pricing_id"]:checked');
    if (firstPlanBtn) firstPlanBtn.dispatchEvent(new Event('change'));
});
</script>
@endsection
