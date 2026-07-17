@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-black tracking-tight mb-8">Shopping Cart</h1>

    @if(isset($items) && count($items) > 0)
        <div class="space-y-4 mb-8">
            @foreach($items as $key => $item)
            @php $allowsQty = in_array($item['product']->allow_quantity ?? 'no', ['separated', 'combined']); @endphp
            <div class="glass rounded-2xl p-5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="server" class="w-6 h-6 text-brand-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold">{{ $item['product']->name ?? 'Product' }}</h3>
                        <p class="text-xs text-gray-500">{{ $item['pricing']->cycle ?? 'Monthly' }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        @if($allowsQty)
                        <div class="flex items-center gap-1">
                            <form method="POST" action="{{ route('cart.update-quantity', $key) }}">
                                @csrf
                                <input type="hidden" name="action" value="decrease">
                                <button type="submit" class="w-7 h-7 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 flex items-center justify-center text-gray-400 hover:text-white transition-all {{ ($item['quantity'] ?? 1) <= 1 ? 'opacity-30 pointer-events-none' : '' }}">
                                    <i data-lucide="minus" class="w-3 h-3"></i>
                                </button>
                            </form>
                            <span class="w-8 text-center text-sm font-semibold">{{ $item['quantity'] ?? 1 }}</span>
                            <form method="POST" action="{{ route('cart.update-quantity', $key) }}">
                                @csrf
                                <input type="hidden" name="action" value="increase">
                                <button type="submit" class="w-7 h-7 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 flex items-center justify-center text-gray-400 hover:text-white transition-all">
                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                </button>
                            </form>
                        </div>
                        @endif
                        <div class="text-right">
                        <span class="text-sm font-semibold">{{ $defaultCurrencySymbol }}{{ number_format($item['line_total'] ?? $item['price'] ?? 0, 2) }}<span class="text-gray-500 font-normal">{{ $item['pricing']->frequency ?? '/mo' }}</span></span>
                        @if(($item['setup_fee'] ?? 0) > 0)
                        <div class="text-[11px] text-amber-400">+{{ $defaultCurrencySymbol }}{{ number_format($item['setup_fee'], 2) }} setup</div>
                        @endif
                        </div>
                        <form id="remove-cart-{{ $key }}" method="POST" action="{{ route('cart.remove', $key) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="showConfirm({title: 'Remove Item', message: 'Remove this item from your cart?', type: 'warning', confirmText: 'Remove', callback: 'remove-cart-{{ $key }}'})" class="p-2 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Summary --}}
        <div class="glass rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Subtotal</span>
                <span class="text-sm font-medium">{{ $defaultCurrencySymbol }}{{ number_format($subtotal ?? 0, 2) }}</span>
            </div>
            @if(($tax ?? 0) > 0)
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Tax</span>
                <span class="text-sm font-medium">{{ $defaultCurrencySymbol }}{{ number_format($tax, 2) }}</span>
            </div>
            @endif
            @if(($promoDiscount ?? 0) > 0)
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Promo Discount</span>
                <span class="text-sm font-medium text-emerald-400">-{{ $defaultCurrencySymbol }}{{ number_format($promoDiscount, 2) }}</span>
            </div>
            @endif
            <hr class="border-white/5">
            <div class="flex items-center justify-between">
                <span class="text-base font-semibold">Total</span>
                <span class="text-xl font-black">{{ $defaultCurrencySymbol }}{{ number_format($total ?? 0, 2) }}<span class="text-sm font-medium text-gray-500">/mo</span></span>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <a href="{{ route('store.index') }}" class="flex-1 py-3 rounded-xl text-sm font-semibold text-gray-400 hover:text-white bg-white/[0.03] hover:bg-white/[0.06] border border-white/10 transition-all text-center">
                    Continue Shopping
                </a>
                <form method="POST" action="{{ route('cart.checkout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full btn-primary py-3 rounded-xl text-sm font-bold text-white shadow-lg shadow-brand-500/20 inline-flex items-center justify-center gap-2">
                        <i data-lucide="credit-card" class="w-4 h-4"></i> Checkout
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="shopping-cart" class="w-8 h-8 text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">Your cart is empty</h3>
            <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">Browse our store to find the perfect plan for you.</p>
            <a href="{{ route('store.index') }}" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Browse Store
            </a>
        </div>
    @endif
</div>
@endsection
