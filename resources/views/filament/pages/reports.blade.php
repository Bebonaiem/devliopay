<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Date Presets + Filter --}}
        <div class="flex flex-wrap items-center gap-2">
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
                        class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all {{ $periodLabel === $label ? 'bg-primary-600 text-white' : 'bg-white dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 border border-gray-200 dark:border-white/10' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Custom Date Range --}}
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
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-500 transition-colors">
                    Apply
                </button>
                <div class="text-xs text-gray-500 dark:text-gray-400 py-2">{{ $periodLabel }}</div>
            </div>
        </form>

        {{-- Revenue Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-emerald-500/10">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-500" />
                    </div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</span>
                </div>
                <div class="text-3xl font-black">${{ $this->getTotalRevenue() }}</div>
                <div class="text-xs text-gray-500 mt-1">Period total</div>
            </div>

            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-500/10">
                        <x-heroicon-o-calendar class="w-5 h-5 text-primary-500" />
                    </div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">This Month</span>
                </div>
                <div class="text-3xl font-black">${{ $this->getMonthlyRevenue() }}</div>
                <div class="text-xs text-gray-500 mt-1">Current month</div>
            </div>

            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-blue-500/10">
                        <x-heroicon-o-calculator class="w-5 h-5 text-blue-500" />
                    </div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Invoice</span>
                </div>
                <div class="text-3xl font-black">${{ $this->getAvgInvoiceValue() }}</div>
                <div class="text-xs text-gray-500 mt-1">Per paid invoice</div>
            </div>

            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-violet-500/10">
                        <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-violet-500" />
                    </div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Conversion</span>
                </div>
                <div class="text-3xl font-black">{{ $stats['conversion_rate'] ?? 0 }}%</div>
                <div class="text-xs text-gray-500 mt-1">Users → Services</div>
            </div>
        </div>

        {{-- Secondary Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1">Orders</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total_orders'] ?? 0) }}</div>
            </div>
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1">Active Services</div>
                <div class="text-2xl font-bold">{{ number_format($stats['active_services'] ?? 0) }}</div>
            </div>
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1">Total Users</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total_users'] ?? 0) }}</div>
                @if(($stats['new_users'] ?? 0) > 0)
                    <div class="text-xs text-emerald-500 mt-0.5">+{{ $stats['new_users'] }} new</div>
                @endif
            </div>
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1">Pending Invoices</div>
                <div class="text-2xl font-bold">{{ number_format($stats['pending_invoices'] ?? 0) }}</div>
                @if(($stats['overdue_invoices'] ?? 0) > 0)
                    <div class="text-xs text-red-500 mt-0.5">{{ $stats['overdue_invoices'] }} overdue</div>
                @endif
            </div>
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="text-xs text-gray-500 mb-1">Total Services</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total_services'] ?? 0) }}</div>
            </div>
        </div>

        {{-- Revenue Chart + Top Products --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Revenue by Month (Bar Chart) --}}
            <div class="lg:col-span-2 fi-card bg-white dark:bg-white/5 rounded-xl shadow-sm border border-gray-200 dark:border-white/10">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-gray-400" />
                        <span class="font-semibold text-sm">Revenue by Month</span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="space-y-2.5">
                        @forelse($revenueByMonth as $row)
                            @php
                                $percentage = $this->maxMonthRevenue > 0 ? ($row['raw'] / $this->maxMonthRevenue) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-10 text-xs text-gray-500 text-right font-medium">{{ $row['month'] }}</div>
                                <div class="flex-1 h-6 bg-gray-100 dark:bg-white/5 rounded-md overflow-hidden">
                                    <div class="h-full rounded-md bg-gradient-to-r from-primary-500 to-primary-400 transition-all duration-500"
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <div class="w-24 text-right text-xs font-semibold">${{ $row['revenue'] }}</div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500 text-sm">No revenue data</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Top Products --}}
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl shadow-sm border border-gray-200 dark:border-white/10">
                <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-trophy class="w-5 h-5 text-gray-400" />
                        <span class="font-semibold text-sm">Top Products</span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="space-y-4">
                        @forelse($topProducts as $i => $product)
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-primary-500/10 text-primary-500 text-[10px] font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                                        <span class="text-sm font-medium">{{ $product['name'] }}</span>
                                    </div>
                                    <span class="text-xs font-semibold">${{ $product['total_revenue'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-primary-500 transition-all duration-500"
                                             style="width: {{ $product['bar_width'] ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-500 w-12 text-right">{{ $product['order_count'] }} orders</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500 text-sm">No product data</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="fi-card bg-white dark:bg-white/5 rounded-xl shadow-sm border border-gray-200 dark:border-white/10">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-shopping-bag class="w-5 h-5 text-gray-400" />
                    <span class="font-semibold text-sm">Recent Orders</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="text-left py-3 px-5 font-medium text-gray-500">Order</th>
                            <th class="text-left py-3 px-5 font-medium text-gray-500">Customer</th>
                            <th class="text-right py-3 px-5 font-medium text-gray-500">Amount</th>
                            <th class="text-left py-3 px-5 font-medium text-gray-500">Status</th>
                            <th class="text-right py-3 px-5 font-medium text-gray-500">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="py-3 px-5 font-mono text-xs">#{{ $order['number'] }}</td>
                                <td class="py-3 px-5">{{ $order['user'] }}</td>
                                <td class="py-3 px-5 text-right font-semibold">${{ $order['total'] }}</td>
                                <td class="py-3 px-5">
                                    <x-filament::badge :color="match($order['status']) {
                                        'completed' => 'success',
                                        'paid' => 'success',
                                        default => 'gray',
                                    }" size="xs">{{ ucfirst($order['status']) }}</x-filament::badge>
                                </td>
                                <td class="py-3 px-5 text-right text-gray-500 text-xs">{{ $order['date'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-gray-500">No recent orders</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
