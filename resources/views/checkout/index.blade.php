@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-black tracking-tight mb-8">Checkout</h1>

    @if(isset($cartItems) && count($cartItems) > 0)
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        {{-- Payment --}}
        <div class="lg:col-span-3 space-y-6">
            <form method="POST" action="{{ route('checkout.process') }}" class="glass rounded-2xl p-6 space-y-6">
                @csrf
                {{-- Payment Method --}}
                <div>
                    <h2 class="text-sm font-semibold mb-4">Payment Method</h2>
                    <div class="space-y-3">
                        @if(($creditBalance ?? 0) > 0)
                        <label class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.02] border border-white/10 hover:border-brand-500/30 cursor-pointer transition-all has-[:checked]:border-brand-500/50 has-[:checked]:bg-brand-500/5">
                            <input type="radio" name="payment_method" value="balance" checked class="sr-only peer">
                            <div class="w-5 h-5 rounded-full border-2 border-gray-600 peer-checked:border-brand-500 flex items-center justify-center"><div class="w-2.5 h-2.5 rounded-full bg-brand-500 scale-0 peer-checked:scale-100 transition-transform"></div></div>
                            <i data-lucide="wallet" class="w-5 h-5 text-gray-400"></i>
                            <div>
                                <p class="text-sm font-medium">Credit Balance</p>
                                <p class="text-xs text-gray-500">Available: ${{ number_format($creditBalance, 2) }}</p>
                            </div>
                        </label>
                        @endif
                        <label class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.02] border border-white/10 hover:border-brand-500/30 cursor-pointer transition-all has-[:checked]:border-brand-500/50 has-[:checked]:bg-brand-500/5">
                            <input type="radio" name="payment_method" value="stripe" class="sr-only peer" {{ ($creditBalance ?? 0) <= 0 ? 'checked' : '' }}>
                            <div class="w-5 h-5 rounded-full border-2 border-gray-600 peer-checked:border-brand-500 flex items-center justify-center"><div class="w-2.5 h-2.5 rounded-full bg-brand-500 scale-0 peer-checked:scale-100 transition-transform"></div></div>
                            <i data-lucide="credit-card" class="w-5 h-5 text-gray-400"></i>
                            <div>
                                <p class="text-sm font-medium">Credit / Debit Card</p>
                                <p class="text-xs text-gray-500">Secure payment via Stripe</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.02] border border-white/10 hover:border-brand-500/30 cursor-pointer transition-all has-[:checked]:border-brand-500/50 has-[:checked]:bg-brand-500/5">
                            <input type="radio" name="payment_method" value="paypal" class="sr-only peer">
                            <div class="w-5 h-5 rounded-full border-2 border-gray-600 peer-checked:border-brand-500 flex items-center justify-center"><div class="w-2.5 h-2.5 rounded-full bg-brand-500 scale-0 peer-checked:scale-100 transition-transform"></div></div>
                            <i data-lucide="globe" class="w-5 h-5 text-gray-400"></i>
                            <div>
                                <p class="text-sm font-medium">PayPal</p>
                                <p class="text-xs text-gray-500">Pay with your PayPal account</p>
                            </div>
                        </label>
                    </div>
                    @error('payment_method') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Promo Code --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Promo Code</label>
                    <div class="flex gap-2">
                        <input type="text" name="promo_code" value="{{ old('promo_code') }}" class="flex-1 bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="Enter code">
                        <button type="button" class="px-5 py-3 rounded-xl text-xs font-semibold text-gray-300 bg-white/[0.03] hover:bg-white/[0.06] border border-white/10 transition-all">Apply</button>
                    </div>
                </div>

                {{-- Terms --}}
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="agree_terms" required class="mt-1 rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500/20">
                    <span class="text-xs text-gray-400">I agree to the <a href="{{ route('terms') }}" target="_blank" class="text-brand-400 hover:text-brand-300">Terms of Service</a> and <a href="{{ route('privacy') }}" target="_blank" class="text-brand-400 hover:text-brand-300">Privacy Policy</a></span>
                </label>
                @error('agree_terms') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

                <button type="submit" class="btn-primary w-full py-3.5 rounded-xl text-sm font-bold text-white shadow-xl shadow-brand-500/25">
                    <i data-lucide="lock" class="w-4 h-4 inline mr-2"></i> Complete Order
                </button>
            </form>
        </div>

        {{-- Order Summary --}}
        <div class="lg:col-span-2">
            <div class="glass rounded-2xl p-6 sticky top-24 space-y-4">
                <h2 class="text-sm font-semibold">Order Summary</h2>
                <div class="space-y-3">
                    @foreach($cartItems as $item)
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-brand-500/10 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="server" class="w-4 h-4 text-brand-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ $item['product']->name ?? 'Product' }}</p>
                            <p class="text-[11px] text-gray-500">{{ $item['pricing']->cycle ?? 'Monthly' }}</p>
                        </div>
                        <span class="text-sm font-semibold">${{ number_format($item['pricing']->price ?? 0, 2) }}</span>
                    </div>
                    @endforeach
                </div>
                <hr class="border-white/5">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">Subtotal</span>
                    <span class="text-sm font-medium">${{ number_format($subtotal ?? 0, 2) }}</span>
                </div>
                @if(($discount ?? 0) > 0)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-emerald-400">Discount</span>
                    <span class="text-sm font-medium text-emerald-400">-${{ number_format($discount, 2) }}</span>
                </div>
                @endif
                @if(($tax ?? 0) > 0)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">Tax</span>
                    <span class="text-sm font-medium">${{ number_format($tax, 2) }}</span>
                </div>
                @endif
                <hr class="border-white/5">
                <div class="flex items-center justify-between">
                    <span class="text-base font-semibold">Total Due Today</span>
                    <span class="text-xl font-black">${{ number_format($total ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="glass rounded-2xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="shopping-cart" class="w-8 h-8 text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">Your cart is empty</h3>
            <p class="text-sm text-gray-400 mb-6">Add some products before checking out.</p>
            <a href="{{ route('store.index') }}" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20">Browse Store</a>
        </div>
    @endif
</div>
@endsection
