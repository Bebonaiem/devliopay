<x-filament-panels::page>
    <form wire:submit="loadStats">
        {{ $this->form }}
    </form>

    <div class="space-y-6 mt-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-success-100 dark:bg-success-900/50">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-success-600 dark:text-success-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ $this->getTotalRevenue() }}</div>
                        <div class="text-xs text-gray-500">Total Revenue</div>
                    </div>
                </div>
            </div>

            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/50">
                        <x-heroicon-o-calendar class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ $this->getMonthlyRevenue() }}</div>
                        <div class="text-xs text-gray-500">Monthly Revenue</div>
                    </div>
                </div>
            </div>

            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-info-100 dark:bg-info-900/50">
                        <x-heroicon-o-calculator class="w-5 h-5 text-info-600 dark:text-info-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ $this->getAvgInvoiceValue() }}</div>
                        <div class="text-xs text-gray-500">Avg Invoice</div>
                    </div>
                </div>
            </div>

            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-warning-100 dark:bg-warning-900/50">
                        <x-heroicon-o-users class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($stats['total_users'] ?? 0) }}</div>
                        <div class="text-xs text-gray-500">Total Users</div>
                    </div>
                </div>
            </div>

            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-success-100 dark:bg-success-900/50">
                        <x-heroicon-o-server class="w-5 h-5 text-success-600 dark:text-success-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($stats['active_services'] ?? 0) }}</div>
                        <div class="text-xs text-gray-500">Active Services</div>
                    </div>
                </div>
            </div>

            <div class="fi-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-warning-100 dark:bg-warning-900/50">
                        <x-heroicon-o-document-text class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($stats['pending_invoices'] ?? 0) }}</div>
                        <div class="text-xs text-gray-500">Pending Invoices</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-gray-400" />
                        <span>Revenue by Month</span>
                    </div>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/10">
                                <th class="text-left py-2 font-medium text-gray-500">Month</th>
                                <th class="text-right py-2 font-medium text-gray-500">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revenueByMonth as $row)
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <td class="py-2">{{ $row['month'] }}</td>
                                    <td class="py-2 text-right font-semibold">${{ $row['revenue'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-4 text-center text-gray-500">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-trophy class="w-5 h-5 text-gray-400" />
                        <span>Top Products</span>
                    </div>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/10">
                                <th class="text-left py-2 font-medium text-gray-500">Product</th>
                                <th class="text-right py-2 font-medium text-gray-500">Orders</th>
                                <th class="text-right py-2 font-medium text-gray-500">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $product)
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <td class="py-2">{{ $product['name'] }}</td>
                                    <td class="py-2 text-right">{{ $product['order_count'] }}</td>
                                    <td class="py-2 text-right font-semibold">${{ $product['total_revenue'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
