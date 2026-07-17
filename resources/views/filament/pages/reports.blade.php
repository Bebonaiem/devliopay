<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Presets + Date Filter --}}
        <div class="fi-card bg-white dark:bg-white/5 rounded-xl shadow-sm border border-gray-200 dark:border-white/10 p-4">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                @php
                    $presets = [
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        'week' => 'This Week',
                        'month' => 'This Month',
                        'quarter' => 'This Quarter',
                        'year' => 'This Year',
                        'last30' => 'Last 30 Days',
                        'last90' => 'Last 90 Days',
                    ];
                @endphp
                @foreach($presets as $key => $label)
                    <button wire:click="setPreset('{{ $key }}')"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all {{ $periodLabel === $label ? 'bg-primary-600 text-white shadow-sm' : 'bg-white dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 border border-gray-200 dark:border-white/10' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <form wire:submit="loadStats">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[150px]">
                        <label class="text-xs font-medium text-gray-500 mb-1 block">Start Date</label>
                        <input type="date" wire:model="data.start_date"
                               class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" />
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="text-xs font-medium text-gray-500 mb-1 block">End Date</label>
                        <input type="date" wire:model="data.end_date"
                               class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 outline-none" />
                    </div>
                    <button type="submit" class="px-5 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-500 transition-colors shadow-sm">
                        Apply
                    </button>
                </div>
            </form>
        </div>

        {{-- Main Stats Row --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="fi-card bg-gradient-to-br from-emerald-500/10 to-emerald-500/5 dark:from-emerald-500/10 dark:to-emerald-500/5 rounded-xl p-5 shadow-sm border border-emerald-500/20">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-500/20 mb-3">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                </div>
                <div class="text-3xl font-black text-emerald-500">${{ $this->getTotalRevenue() }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Revenue</div>
            </div>

            <div class="fi-card bg-gradient-to-br from-primary-500/10 to-primary-500/5 dark:from-primary-500/10 dark:to-primary-500/5 rounded-xl p-5 shadow-sm border border-primary-500/20">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-500/20 mb-3">
                    <x-heroicon-o-calendar class="w-5 h-5 text-primary-500" />
                </div>
                <div class="text-3xl font-black text-primary-500">${{ $this->getMonthlyRevenue() }}</div>
                <div class="text-xs text-gray-500 mt-1">This Month</div>
            </div>

            <div class="fi-card bg-gradient-to-br from-blue-500/10 to-blue-500/5 dark:from-blue-500/10 dark:to-blue-500/5 rounded-xl p-5 shadow-sm border border-blue-500/20">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-500/20 mb-3">
                    <x-heroicon-o-calculator class="w-5 h-5 text-blue-500" />
                </div>
                <div class="text-3xl font-black text-blue-500">${{ $this->getAvgInvoiceValue() }}</div>
                <div class="text-xs text-gray-500 mt-1">Avg Invoice</div>
            </div>

            <div class="fi-card bg-gradient-to-br from-violet-500/10 to-violet-500/5 dark:from-violet-500/10 dark:to-violet-500/5 rounded-xl p-5 shadow-sm border border-violet-500/20">
                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-violet-500/20 mb-3">
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-violet-500" />
                </div>
                <div class="text-3xl font-black text-violet-500">{{ $stats['conversion_rate'] ?? 0 }}%</div>
                <div class="text-xs text-gray-500 mt-1">Conversion Rate</div>
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1 font-medium">Orders</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total_orders'] ?? 0) }}</div>
            </div>
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1 font-medium">Active Services</div>
                <div class="text-2xl font-bold text-emerald-500">{{ number_format($stats['active_services'] ?? 0) }}</div>
            </div>
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1 font-medium">Total Users</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total_users'] ?? 0) }}</div>
                @if(($stats['new_users'] ?? 0) > 0)
                    <div class="text-xs text-emerald-500 mt-0.5">+{{ $stats['new_users'] }} new</div>
                @endif
            </div>
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1 font-medium">Pending</div>
                <div class="text-2xl font-bold text-amber-500">{{ number_format($stats['pending_invoices'] ?? 0) }}</div>
                @if(($stats['overdue_invoices'] ?? 0) > 0)
                    <div class="text-xs text-red-500 mt-0.5">{{ $stats['overdue_invoices'] }} overdue</div>
                @endif
            </div>
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1 font-medium">Total Services</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total_services'] ?? 0) }}</div>
            </div>
        </div>

        {{-- Revenue Chart + Top Products Side by Side --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Revenue by Month --}}
            <div class="lg:col-span-2 fi-card bg-white dark:bg-white/5 rounded-xl shadow-sm border border-gray-200 dark:border-white/10">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary-500/10 flex items-center justify-center">
                        <x-heroicon-o-chart-bar class="w-4 h-4 text-primary-500" />
                    </div>
                    <span class="font-semibold text-sm">Revenue by Month</span>
                </div>
                <div class="p-5">
                    <div class="space-y-2">
                        @forelse($revenueByMonth as $row)
                            @php
                                $percentage = $this->maxMonthRevenue > 0 ? ($row['raw'] / $this->maxMonthRevenue) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3 group">
                                <div class="w-8 text-[11px] text-gray-500 text-right font-medium">{{ $row['month'] }}</div>
                                <div class="flex-1 h-7 bg-gray-100 dark:bg-white/5 rounded-md overflow-hidden">
                                    <div class="h-full rounded-md bg-gradient-to-r from-primary-600 to-primary-400 transition-all duration-500 flex items-center justify-end pr-2"
                                         style="width: {{ max($percentage, 2) }}%">
                                        @if($percentage > 15)
                                            <span class="text-[10px] font-bold text-white">${{ $row['revenue'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if($percentage <= 15)
                                    <div class="w-20 text-right text-[11px] font-semibold text-gray-600 dark:text-gray-400">${{ $row['revenue'] }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-12 text-gray-500 text-sm">No revenue data</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Top Products --}}
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl shadow-sm border border-gray-200 dark:border-white/10">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                        <x-heroicon-o-trophy class="w-4 h-4 text-amber-500" />
                    </div>
                    <span class="font-semibold text-sm">Top Products</span>
                </div>
                <div class="p-5">
                    @forelse($topProducts as $i => $product)
                        <div class="{{ !$loop->first ? 'mt-4 pt-4 border-t border-gray-100 dark:border-white/5' : '' }}">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white text-[10px] font-bold flex items-center justify-center shadow-sm">{{ $i + 1 }}</span>
                                    <span class="text-sm font-semibold">{{ $product['name'] }}</span>
                                </div>
                                <span class="text-sm font-bold text-emerald-500">${{ $product['total_revenue'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-amber-400 transition-all duration-500"
                                         style="width: {{ $product['bar_width'] ?? 0 }}%"></div>
                                </div>
                                <span class="text-[10px] text-gray-500 w-14 text-right">{{ $product['order_count'] }} sold</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-500 text-sm">No product data</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="fi-card bg-white dark:bg-white/5 rounded-xl shadow-sm border border-gray-200 dark:border-white/10">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <x-heroicon-o-shopping-bag class="w-4 h-4 text-blue-500" />
                    </div>
                    <span class="font-semibold text-sm">Recent Orders</span>
                </div>
                <a href="/admin/orders" class="text-xs text-primary-500 hover:text-primary-400 font-medium transition-colors">View All &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="text-left py-3 px-5 font-medium text-gray-500 text-xs uppercase tracking-wider">Order</th>
                            <th class="text-left py-3 px-5 font-medium text-gray-500 text-xs uppercase tracking-wider">Customer</th>
                            <th class="text-right py-3 px-5 font-medium text-gray-500 text-xs uppercase tracking-wider">Amount</th>
                            <th class="text-left py-3 px-5 font-medium text-gray-500 text-xs uppercase tracking-wider">Status</th>
                            <th class="text-right py-3 px-5 font-medium text-gray-500 text-xs uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                <td class="py-3.5 px-5 font-mono text-xs font-medium">#{{ $order['number'] }}</td>
                                <td class="py-3.5 px-5 font-medium">{{ $order['user'] }}</td>
                                <td class="py-3.5 px-5 text-right font-bold">${{ $order['total'] }}</td>
                                <td class="py-3.5 px-5">
                                    <x-filament::badge :color="match($order['status']) {
                                        'completed' => 'success',
                                        'paid' => 'success',
                                        default => 'gray',
                                    }" size="xs">{{ ucfirst($order['status']) }}</x-filament::badge>
                                </td>
                                <td class="py-3.5 px-5 text-right text-gray-500 text-xs">{{ $order['date'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-500">No recent orders</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
