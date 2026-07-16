@extends('layouts.client')

@section('title', 'Orders')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Orders</h1>
        <p class="text-sm text-gray-400 mt-1">View your order history.</p>
    </div>

    @if(isset($orders) && count($orders) > 0)
        <div class="glass rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Order</th>
                            <th class="text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Product</th>
                            <th class="text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Date</th>
                            <th class="text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Total</th>
                            <th class="text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Status</th>
                            <th class="text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($orders as $order)
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold">#{{ $order->order_number ?? $order->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-300">{{ $order->product->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-400">{{ $order->created_at ? $order->created_at->format('M j, Y') : '-' }}</td>
                            <td class="px-6 py-4 text-sm font-semibold">${{ number_format($order->total ?? 0, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider
                                    {{ ($order->status ?? '') === 'completed' ? 'bg-emerald-500/10 text-emerald-400' : '' }}
                                    {{ ($order->status ?? '') === 'pending' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                    {{ ($order->status ?? '') === 'cancelled' ? 'bg-red-500/10 text-red-400' : '' }}
                                    {{ !in_array($order->status ?? '', ['completed','pending','cancelled']) ? 'bg-gray-500/10 text-gray-400' : '' }}
                                ">{{ $order->status ?? 'unknown' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('client.orders.show') }}?id={{ $order->id }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="shopping-bag" class="w-8 h-8 text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">No orders found</h3>
            <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">You haven't placed any orders yet.</p>
            <a href="{{ route('store.index') }}" class="btn-primary px-6 py-2.5 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Browse Store
            </a>
        </div>
    @endif
</div>
@endsection
