@extends('layouts.app')

@section('title', 'Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-3">Browse Our Plans</h1>
        <p class="text-gray-400 max-w-lg mx-auto">Find the perfect hosting solution for your needs.</p>
    </div>

    {{-- Category Filters --}}
    @if(isset($categories) && count($categories) > 0)
    <div class="flex flex-wrap justify-center gap-2 mb-10">
        <a href="{{ route('store.index') }}" class="px-5 py-2 rounded-xl text-xs font-semibold transition-all {{ !request('category') ? 'bg-brand-500/15 text-brand-400 border border-brand-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">All</a>
        @foreach($categories as $category)
        <a href="{{ route('store.index', ['category' => $category->slug]) }}" class="px-5 py-2 rounded-xl text-xs font-semibold transition-all {{ request('category') === $category->slug ? 'bg-brand-500/15 text-brand-400 border border-brand-500/20' : 'text-gray-400 hover:text-white border border-white/5 hover:border-white/10' }}">{{ $category->name }}</a>
        @endforeach
    </div>
    @endif

    {{-- Products --}}
    @if(isset($products) && count($products) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($products as $product)
            <div class="glass rounded-2xl overflow-hidden card-hover group">
                {{-- Image --}}
                <div class="relative h-48 bg-gradient-to-br from-brand-500/10 to-purple-500/10 overflow-hidden">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i data-lucide="server" class="w-12 h-12 text-brand-400/30"></i>
                        </div>
                    @endif
                    @if($product->category)
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-black/40 backdrop-blur-sm text-gray-300 border border-white/10">{{ $product->category->name }}</span>
                    </div>
                    @endif
                </div>
                {{-- Content --}}
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-2 group-hover:text-brand-400 transition-colors">{{ $product->name }}</h3>
                    <p class="text-sm text-gray-400 line-clamp-2 mb-4">{!! nl2br(e($product->description ?? 'High-performance hosting solution.')) !!}</p>
                    <div class="flex items-end justify-between">
                        <div>
                            <span class="text-xs text-gray-500">Starting from</span>
                            <div class="text-2xl font-black">{{ $product->pricing->first()?->currencies->first()?->symbol ?? $defaultCurrencySymbol }}{{ number_format($product->pricing->min('price') ?? $product->base_price ?? 0, 2) }}<span class="text-sm font-medium text-gray-500">/mo</span></div>
                        </div>
                        <a href="{{ route('store.show', $product->slug) }}" class="btn-primary px-5 py-2.5 rounded-xl text-xs font-semibold text-white shadow-lg shadow-brand-500/20">
                            Configure
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="package" class="w-8 h-8 text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">No products available</h3>
            <p class="text-sm text-gray-400">Check back soon for new products!</p>
        </div>
    @endif
</div>
@endsection
