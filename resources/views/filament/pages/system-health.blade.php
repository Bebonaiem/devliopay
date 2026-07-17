<x-filament::page>
    <div class="space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm">
                <div class="text-sm text-gray-500">Total Users</div>
                <div class="text-2xl font-bold">{{ number_format($stats['total_users']) }}</div>
            </div>
            <div class="bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm">
                <div class="text-sm text-gray-500">Active Services</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['active_services']) }}</div>
            </div>
            <div class="bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm">
                <div class="text-sm text-gray-500">Revenue This Month</div>
                <div class="text-2xl font-bold">{{ $currencySymbol }}{{ number_format($stats['revenue_this_month'], 2) }}</div>
            </div>
            <div class="bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm">
                <div class="text-sm text-gray-500">Pending Invoices</div>
                <div class="text-2xl font-bold {{ $stats['overdue_invoices'] > 0 ? 'text-red-600' : '' }}">
                    {{ number_format($stats['pending_invoices']) }}
                    @if($stats['overdue_invoices'] > 0)
                        <span class="text-sm text-red-500">({{ $stats['overdue_invoices'] }} overdue)</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4">Revenue Comparison</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">This Month</span>
                        <span class="font-semibold">{{ $currencySymbol }}{{ number_format($stats['revenue_this_month'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Last Month</span>
                        <span class="font-semibold">{{ $currencySymbol }}{{ number_format($stats['revenue_last_month'], 2) }}</span>
                    </div>
                    @php $change = $stats['revenue_last_month'] > 0 ? (($stats['revenue_this_month'] - $stats['revenue_last_month']) / $stats['revenue_last_month']) * 100 : 0; @endphp
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Change</span>
                        <span class="font-semibold {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 1) }}%
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4">Recent Orders</h3>
                <div class="space-y-3">
                    @forelse($recentActivity['recent_orders'] as $order)
                        <div class="flex justify-between items-center text-sm">
                            <div>
                                <span class="font-medium">{{ $order['number'] }}</span>
                                <span class="text-gray-500"> - {{ $order['user'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-medium">{{ $currencySymbol }}{{ number_format($order['total'], 2) }}</span>
                                <span class="text-xs ml-1 px-2 py-0.5 rounded-full
                                    {{ $order['status'] === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' }}">
                                    {{ ucfirst($order['status']) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No recent orders</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4">System Information</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">PHP Version</span>
                        <span>{{ $systemInfo['php_version'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Laravel Version</span>
                        <span>{{ $systemInfo['laravel_version'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Database</span>
                        <span>{{ ucfirst($systemInfo['database_driver']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Cache</span>
                        <span>{{ ucfirst($systemInfo['cache_driver']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Queue</span>
                        <span>{{ ucfirst($systemInfo['queue_driver']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Disk Usage</span>
                        <span>{{ $systemInfo['disk_usage']['used'] }} / {{ $systemInfo['disk_usage']['total'] }} ({{ $systemInfo['disk_usage']['percent'] }}%)</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4">PHP Extensions</h3>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($systemInfo['extensions'] as $name => $loaded)
                        <div class="flex items-center text-sm">
                            <span class="{{ $loaded ? 'text-green-500' : 'text-red-500' }} mr-2">
                                <i class="fas fa-{{ $loaded ? 'check-circle' : 'times-circle' }}"></i>
                            </span>
                            <span class="{{ $loaded ? '' : 'text-gray-400' }}">{{ $name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
