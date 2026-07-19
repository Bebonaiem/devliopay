<x-filament::page>
    @php
        $healthDot = fn($s) => match($s) { 'success' => 'bg-green-500', 'warning' => 'bg-yellow-500', 'danger' => 'bg-red-500', default => 'bg-gray-500' };
        $healthLabel = fn($s) => match($s) { 'success' => 'Healthy', 'warning' => 'Warning', 'danger' => 'Critical', default => 'Unknown' };
    @endphp

    <div class="space-y-6">

        {{-- Health Status Overview --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($healthChecks as $key => $check)
                <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm border-l-4 {{ match($check['status']) { 'success' => 'border-green-500', 'warning' => 'border-yellow-500', 'danger' => 'border-red-500', default => 'border-gray-500' } }}">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full {{ $healthDot($check['status']) }}"></div>
                        <div>
                            <div class="text-xs text-white">{{ $check['label'] }}</div>
                            <div class="text-sm font-semibold {{ match($check['status']) { 'success' => 'text-green-600', 'warning' => 'text-yellow-600', 'danger' => 'text-red-600', default => 'text-white' } }}">{{ $healthLabel($check['status']) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Health Checks Detail --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($healthChecks as $key => $check)
                        <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-2.5 h-2.5 rounded-full {{ $healthDot($check['status']) }}"></div>
                                <h3 class="font-semibold text-sm text-white">{{ $check['label'] }}</h3>
                                <span class="ml-auto text-xs {{ match($check['status']) { 'success' => 'text-green-600', 'warning' => 'text-yellow-600', 'danger' => 'text-red-600', default => 'text-white' } }}">{{ $healthLabel($check['status']) }}</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($check['checks'] as $sub)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-white">{{ $sub['label'] }}</span>
                                        <span class="flex items-center gap-1.5">
                                            @if($sub['ok'])
                                                <span class="text-green-500">&#10003;</span>
                                            @else
                                                <span class="text-red-500">&#10007;</span>
                                            @endif
                                            <span class="{{ $sub['ok'] ? 'text-white' : 'text-red-600' }}">{{ $sub['info'] }}</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Recent Activity Tabs --}}
                <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                    <div x-data="{ tab: 'orders' }">
                        <div class="flex items-center gap-1 mb-4 border-b border-gray-200 dark:border-white/10 pb-3">
                            <button @click="tab = 'orders'" :class="{ 'bg-gray-100 dark:bg-white/10 font-medium': tab === 'orders' }" class="px-3 py-1.5 text-sm rounded-lg transition text-white">Recent Orders</button>
                            <button @click="tab = 'tickets'" :class="{ 'bg-gray-100 dark:bg-white/10 font-medium': tab === 'tickets' }" class="px-3 py-1.5 text-sm rounded-lg transition text-white">Recent Tickets</button>
                            <button @click="tab = 'transactions'" :class="{ 'bg-gray-100 dark:bg-white/10 font-medium': tab === 'transactions' }" class="px-3 py-1.5 text-sm rounded-lg transition text-white">Recent Payments</button>
                        </div>

                        {{-- Orders Tab --}}
                        <div x-show="tab === 'orders'" class="space-y-2">
                            @forelse($recentActivity['recent_orders'] as $order)
                                <a href="{{ route('filament.admin.resources.orders.edit', $order['id']) }}" class="flex items-center justify-between p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition text-sm group">
                                    <div>
                                        <span class="font-medium text-white">{{ $order['number'] }}</span>
                                        <span class="text-white"> - {{ $order['user'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-white">{{ $currencySymbol }}{{ number_format($order['total'], 2) }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $order['status'] === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' }}">
                                            {{ ucfirst($order['status']) }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <p class="text-white text-sm text-center py-4">No recent orders</p>
                            @endforelse
                        </div>

                        {{-- Tickets Tab --}}
                        <div x-show="tab === 'tickets'" class="space-y-2">
                            @forelse($recentActivity['recent_tickets'] as $ticket)
                                <a href="{{ route('filament.admin.resources.ticket-threads.edit', $ticket['id']) }}" class="flex items-center justify-between p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition text-sm group">
                                    <div>
                                        <span class="font-medium text-white">{{ $ticket['number'] }}</span>
                                        <span class="text-white"> - {{ $ticket['subject'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $ticket['priority'] === 'high' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : ($ticket['priority'] === 'medium' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' : 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400') }}">
                                            {{ ucfirst($ticket['priority']) }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <p class="text-white text-sm text-center py-4">No recent tickets</p>
                            @endforelse
                        </div>

                        {{-- Transactions Tab --}}
                        <div x-show="tab === 'transactions'" class="space-y-2">
                            @forelse($recentActivity['recent_transactions'] as $txn)
                                <div class="flex items-center justify-between p-2.5 rounded-lg text-sm">
                                    <div>
                                        <span class="text-white">{{ $txn['user'] }}</span>
                                        <span class="text-white text-xs ml-1 uppercase">({{ $txn['gateway'] }})</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-medium text-green-600">{{ $currencySymbol }}{{ number_format($txn['amount'], 2) }}</span>
                                        <span class="text-white text-xs">{{ $txn['date'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-white text-sm text-center py-4">No recent payments</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Sidebar --}}
            <div class="space-y-6">

                {{-- Key Metrics --}}
                <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 text-white">Platform Metrics</h3>
                    <div class="space-y-3">
                        @php
                            $metrics = [
                                ['label' => 'Total Revenue', 'value' => $currencySymbol . number_format($stats['total_revenue'], 2), 'color' => 'text-green-600'],
                                ['label' => 'Active Services', 'value' => number_format($stats['active_services']), 'color' => 'text-green-600'],
                                ['label' => 'Total Users', 'value' => number_format($stats['total_users']), 'color' => 'text-white'],
                                ['label' => 'Open Tickets', 'value' => number_format($stats['open_tickets']), 'color' => 'text-yellow-600'],
                                ['label' => 'Pending Invoices', 'value' => number_format($stats['pending_invoices']), 'color' => 'text-yellow-600'],
                                ['label' => 'New This Month', 'value' => number_format($stats['new_users_this_month']) . ' users / ' . number_format($stats['new_orders_this_month']) . ' orders', 'color' => 'text-white'],
                            ];
                        @endphp
                        @foreach($metrics as $m)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-white/5">
                                <span class="text-sm text-white">{{ $m['label'] }}</span>
                                <span class="text-sm font-semibold {{ $m['color'] }}">{{ $m['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Revenue Trend --}}
                <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 text-white">Revenue Trend</h3>
                    <div class="space-y-3">
                        @php $maxRevenue = max($stats['revenue_this_month'], $stats['revenue_last_month'], 1); @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-white">This Month</span>
                                <span class="font-semibold text-white">{{ $currencySymbol }}{{ number_format($stats['revenue_this_month'], 2) }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full" style="width: {{ ($stats['revenue_this_month'] / $maxRevenue) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-white">Last Month</span>
                                <span class="font-semibold text-white">{{ $currencySymbol }}{{ number_format($stats['revenue_last_month'], 2) }}</span>
                            </div>
                            <div class="w-full h-2.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gray-400 dark:bg-gray-600 rounded-full" style="width: {{ ($stats['revenue_last_month'] / $maxRevenue) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-white/10">
                            <span class="text-sm text-white">Change</span>
                            <span class="text-sm font-semibold {{ $stats['revenue_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $stats['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($stats['revenue_change'], 1) }}%
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Disk Usage --}}
                <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-3 text-white">Disk Usage</h3>
                    <div class="mb-3">
                        @php $pct = $systemInfo['disk_usage']['percent']; $barColor = $pct >= 90 ? 'bg-red-500' : ($pct >= 80 ? 'bg-yellow-500' : 'bg-green-500'); @endphp
                        <div class="w-full h-3 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full {{ $barColor }} rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-sm">
                        <div>
                            <div class="text-white text-xs">Used</div>
                            <div class="font-semibold text-white">{{ $systemInfo['disk_usage']['used'] }}</div>
                        </div>
                        <div>
                            <div class="text-white text-xs">Free</div>
                            <div class="font-semibold text-white">{{ $systemInfo['disk_usage']['free'] }}</div>
                        </div>
                        <div>
                            <div class="text-white text-xs">Total</div>
                            <div class="font-semibold text-white">{{ $systemInfo['disk_usage']['total'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- PHP Extensions --}}
                <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-3 text-white">PHP Extensions</h3>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($systemInfo['extensions'] as $name => $loaded)
                            <div class="flex items-center text-sm">
                                <span class="{{ $loaded ? 'text-green-500' : 'text-red-500' }} mr-2">&#10003;</span>
                                <span class="{{ $loaded ? 'text-white' : 'text-white' }}">{{ $name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- System Info --}}
                <div class="bg-white dark:bg-white/5 rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-3 text-white">System Details</h3>
                    <div class="space-y-2 text-sm">
                        @php
                            $sys = [
                                ['PHP Version', $systemInfo['php_version']],
                                ['Laravel Version', $systemInfo['laravel_version']],
                                ['Environment', ucfirst($systemInfo['environment'])],
                                ['Timezone', $systemInfo['timezone']],
                                ['Database', ucfirst($systemInfo['database_driver'])],
                                ['Cache Driver', ucfirst($systemInfo['cache_driver'])],
                                ['Queue Driver', ucfirst($systemInfo['queue_driver'])],
                                ['Session Driver', ucfirst($systemInfo['session_driver'])],
                                ['Mail Driver', ucfirst($systemInfo['mail_driver'])],
                                ['APP_URL', $systemInfo['app_url'] ?: 'Not set'],
                                ['Memory Limit', $systemInfo['memory_limit']],
                                ['Max Upload', $systemInfo['max_upload_size']],
                                ['Max Execution', $systemInfo['max_execution_time']],
                                ['Post Max Size', $systemInfo['post_max_size']],
                            ];
                        @endphp
                        @foreach($sys as $s)
                            <div class="flex justify-between py-1 {{ !$loop->last ? 'border-b border-gray-100 dark:border-white/5' : '' }}">
                                <span class="text-white">{{ $s[0] }}</span>
                                <span class="font-medium text-white text-right">{{ $s[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
