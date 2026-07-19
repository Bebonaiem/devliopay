<x-filament::page>
    @php
        $healthStatusColor = fn($s) => match($s) { 'success' => 'bg-success-500', 'warning' => 'bg-warning-500', 'danger' => 'bg-danger-500', default => 'bg-gray-500' };
        $healthTextColor = fn($s) => match($s) { 'success' => 'text-success-500', 'warning' => 'text-warning-500', 'danger' => 'text-danger-500', default => 'text-gray-500' };
        $healthBgColor = fn($s) => match($s) { 'success' => 'bg-success-500/10 border-success-500/30 text-success-500', 'warning' => 'bg-warning-500/10 border-warning-500/30 text-warning-500', 'danger' => 'bg-danger-500/10 border-danger-500/30 text-danger-500', default => 'bg-gray-500/10 border-gray-500/30 text-gray-500' };
        $healthLabel = fn($s) => match($s) { 'success' => 'Healthy', 'warning' => 'Warning', 'danger' => 'Critical', default => 'Unknown' };
    @endphp

    <div class="space-y-6">

        {{-- Health Status Overview --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($healthChecks as $key => $check)
                <div class="flex items-center gap-3 rounded-xl border p-4 {{ $healthBgColor($check['status']) }}">
                    <div class="flex-shrink-0 w-3 h-3 rounded-full {{ $healthStatusColor($check['status']) }}"></div>
                    <div>
                        <div class="text-xs font-medium opacity-75">{{ $check['label'] }}</div>
                        <div class="text-sm font-semibold">{{ $healthLabel($check['status']) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Detailed Health Checks --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Health Checks Detail --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($healthChecks as $key => $check)
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 p-5">
                            <div class="flex items-center gap-2 mb-4">
                                <div class="w-2.5 h-2.5 rounded-full {{ $healthStatusColor($check['status']) }}"></div>
                                <h3 class="font-semibold text-sm">{{ $check['label'] }}</h3>
                                <span class="ml-auto text-xs {{ $healthTextColor($check['status']) }}">{{ $healthLabel($check['status']) }}</span>
                            </div>
                            <div class="space-y-2">
                                @foreach($check['checks'] as $sub)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-400">{{ $sub['label'] }}</span>
                                        <span class="flex items-center gap-1.5">
                                            @if($sub['ok'])
                                                <x-heroicon-o-check-circle class="w-3.5 h-3.5 text-success-500" />
                                            @else
                                                <x-heroicon-o-x-circle class="w-3.5 h-3.5 text-danger-500" />
                                            @endif
                                            <span class="{{ $sub['ok'] ? 'text-gray-700 dark:text-gray-300' : 'text-danger-500' }}">{{ $sub['info'] }}</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Recent Activity Tabs --}}
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-5">
                    <div x-data="{ tab: 'orders' }">
                        <div class="flex items-center gap-1 mb-4 border-b border-gray-200 dark:border-white/10 pb-3">
                            <button @click="tab = 'orders'" :class="{ 'bg-gray-100 dark:bg-white/10 font-medium': tab === 'orders' }" class="px-3 py-1.5 text-xs rounded-lg transition">Recent Orders</button>
                            <button @click="tab = 'tickets'" :class="{ 'bg-gray-100 dark:bg-white/10 font-medium': tab === 'tickets' }" class="px-3 py-1.5 text-xs rounded-lg transition">Recent Tickets</button>
                            <button @click="tab = 'transactions'" :class="{ 'bg-gray-100 dark:bg-white/10 font-medium': tab === 'transactions' }" class="px-3 py-1.5 text-xs rounded-lg transition">Recent Payments</button>
                        </div>

                        {{-- Orders Tab --}}
                        <div x-show="tab === 'orders'" class="space-y-2">
                            @forelse($recentActivity['recent_orders'] as $order)
                                <a href="{{ route('filament.admin.resources.orders.edit', $order['id']) }}" class="flex items-center justify-between p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition text-sm group">
                                    <div class="flex items-center gap-3">
                                        <span class="font-mono text-xs font-medium">{{ $order['number'] }}</span>
                                        <span class="text-gray-500 text-xs">{{ $order['user'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-medium text-xs">{{ $currencySymbol }}{{ number_format($order['total'], 2) }}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $order['status'] === 'completed' ? 'bg-success-500/10 text-success-600' : ($order['status'] === 'pending' ? 'bg-warning-500/10 text-warning-600' : 'bg-gray-500/10 text-gray-600') }}">
                                            {{ ucfirst($order['status']) }}
                                        </span>
                                        <x-heroicon-o-chevron-right class="w-3.5 h-3.5 text-gray-400 opacity-0 group-hover:opacity-100 transition" />
                                    </div>
                                </a>
                            @empty
                                <p class="text-gray-500 text-xs text-center py-4">No recent orders</p>
                            @endforelse
                        </div>

                        {{-- Tickets Tab --}}
                        <div x-show="tab === 'tickets'" class="space-y-2">
                            @forelse($recentActivity['recent_tickets'] as $ticket)
                                <a href="{{ route('filament.admin.resources.ticket-threads.edit', $ticket['id']) }}" class="flex items-center justify-between p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition text-sm group">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="font-mono text-xs font-medium flex-shrink-0">{{ $ticket['number'] }}</span>
                                        <span class="text-gray-500 text-xs truncate">{{ $ticket['subject'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-xs px-2 py-0.5 rounded-full {{ $ticket['priority'] === 'high' ? 'bg-danger-500/10 text-danger-600' : ($ticket['priority'] === 'medium' ? 'bg-warning-500/10 text-warning-600' : 'bg-gray-500/10 text-gray-600') }}">
                                            {{ ucfirst($ticket['priority']) }}
                                        </span>
                                        <x-heroicon-o-chevron-right class="w-3.5 h-3.5 text-gray-400 opacity-0 group-hover:opacity-100 transition" />
                                    </div>
                                </a>
                            @empty
                                <p class="text-gray-500 text-xs text-center py-4">No recent tickets</p>
                            @endforelse
                        </div>

                        {{-- Transactions Tab --}}
                        <div x-show="tab === 'transactions'" class="space-y-2">
                            @forelse($recentActivity['recent_transactions'] as $txn)
                                <div class="flex items-center justify-between p-2.5 rounded-lg text-sm">
                                    <div class="flex items-center gap-3">
                                        <span class="text-gray-500 text-xs">{{ $txn['user'] }}</span>
                                        <span class="text-xs text-gray-400 uppercase">{{ $txn['gateway'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-medium text-xs text-success-600">{{ $currencySymbol }}{{ number_format($txn['amount'], 2) }}</span>
                                        <span class="text-xs text-gray-400">{{ $txn['date'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-xs text-center py-4">No recent payments</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Sidebar --}}
            <div class="space-y-6">

                {{-- Key Metrics --}}
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-5">
                    <h3 class="font-semibold text-sm mb-4">Platform Metrics</h3>
                    <div class="space-y-3">
                        @php
                            $metrics = [
                                ['label' => 'Total Revenue', 'value' => $currencySymbol . number_format($stats['total_revenue'], 2), 'icon' => 'heroicon-o-currency-dollar', 'color' => 'text-success-500'],
                                ['label' => 'Active Services', 'value' => number_format($stats['active_services']), 'icon' => 'heroicon-o-server', 'color' => 'text-primary-500'],
                                ['label' => 'Total Users', 'value' => number_format($stats['total_users']), 'icon' => 'heroicon-o-users', 'color' => 'text-info-500'],
                                ['label' => 'Open Tickets', 'value' => number_format($stats['open_tickets']), 'icon' => 'heroicon-o-chat-bubble-left-right', 'color' => 'text-warning-500'],
                                ['label' => 'Pending Invoices', 'value' => number_format($stats['pending_invoices']), 'icon' => 'heroicon-o-document-text', 'color' => 'text-danger-500'],
                                ['label' => 'New This Month', 'value' => number_format($stats['new_users_this_month']) . ' users / ' . number_format($stats['new_orders_this_month']) . ' orders', 'icon' => 'heroicon-o-arrow-trending-up', 'color' => 'text-primary-500'],
                            ];
                        @endphp
                        @foreach($metrics as $m)
                            <div class="flex items-center gap-3 p-2.5 rounded-lg bg-gray-50 dark:bg-white/5">
                                <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center bg-gray-200/50 dark:bg-white/10">
                                    <x-dynamic-component :component="$m['icon']" class="w-4 h-4 {{ $m['color'] }}" />
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs text-gray-500">{{ $m['label'] }}</div>
                                    <div class="text-sm font-semibold truncate">{{ $m['value'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Revenue Trend --}}
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-5">
                    <h3 class="font-semibold text-sm mb-3">Revenue Trend</h3>
                    <div class="space-y-3">
                        @php $maxRevenue = max($stats['revenue_this_month'], $stats['revenue_last_month'], 1); @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500">This Month</span>
                                <span class="font-semibold">{{ $currencySymbol }}{{ number_format($stats['revenue_this_month'], 2) }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full bg-success-500 rounded-full transition-all" style="width: {{ ($stats['revenue_this_month'] / $maxRevenue) * 100 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500">Last Month</span>
                                <span class="font-semibold">{{ $currencySymbol }}{{ number_format($stats['revenue_last_month'], 2) }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full bg-gray-400 dark:bg-gray-600 rounded-full transition-all" style="width: {{ ($stats['revenue_last_month'] / $maxRevenue) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center pt-1 border-t border-gray-200 dark:border-white/10">
                            <span class="text-xs text-gray-500">Change</span>
                            <span class="text-xs font-semibold {{ $stats['revenue_change'] >= 0 ? 'text-success-500' : 'text-danger-500' }}">
                                {{ $stats['revenue_change'] >= 0 ? '+' : '' }}{{ number_format($stats['revenue_change'], 1) }}%
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Disk Usage --}}
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-5">
                    <h3 class="font-semibold text-sm mb-3">Disk Usage</h3>
                    <div class="mb-3">
                        @php $pct = $systemInfo['disk_usage']['percent']; $barColor = $pct >= 90 ? 'bg-danger-500' : ($pct >= 80 ? 'bg-warning-500' : 'bg-success-500'); @endphp
                        <div class="w-full h-3 bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div>
                            <div class="text-gray-500">Used</div>
                            <div class="font-semibold">{{ $systemInfo['disk_usage']['used'] }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Free</div>
                            <div class="font-semibold">{{ $systemInfo['disk_usage']['free'] }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Total</div>
                            <div class="font-semibold">{{ $systemInfo['disk_usage']['total'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- PHP Extensions --}}
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-5">
                    <h3 class="font-semibold text-sm mb-3">PHP Extensions</h3>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($systemInfo['extensions'] as $name => $loaded)
                            <div class="flex items-center gap-2 p-2 rounded-lg {{ $loaded ? 'bg-success-500/5' : 'bg-danger-500/5' }}">
                                @if($loaded)
                                    <x-heroicon-o-check-circle class="w-3.5 h-3.5 text-success-500 flex-shrink-0" />
                                @else
                                    <x-heroicon-o-x-circle class="w-3.5 h-3.5 text-danger-500 flex-shrink-0" />
                                @endif
                                <span class="text-xs {{ $loaded ? 'text-gray-700 dark:text-gray-300' : 'text-danger-500 line-through' }}">{{ $name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- System Info --}}
                <div class="rounded-xl border border-gray-200 dark:border-white/10 p-5">
                    <h3 class="font-semibold text-sm mb-3">System Details</h3>
                    <div class="space-y-2 text-xs">
                        @php
                            $sys = [
                                ['PHP', $systemInfo['php_version']],
                                ['Laravel', $systemInfo['laravel_version']],
                                ['Environment', ucfirst($systemInfo['environment'])],
                                ['Timezone', $systemInfo['timezone']],
                                ['Database', ucfirst($systemInfo['database_driver'])],
                                ['Cache', ucfirst($systemInfo['cache_driver'])],
                                ['Queue', ucfirst($systemInfo['queue_driver'])],
                                ['Session', ucfirst($systemInfo['session_driver'])],
                                ['Mail', ucfirst($systemInfo['mail_driver'])],
                                ['APP_URL', $systemInfo['app_url'] ?: 'Not set'],
                                ['Memory Limit', $systemInfo['memory_limit']],
                                ['Max Upload', $systemInfo['max_upload_size']],
                                ['Max Exec', $systemInfo['max_execution_time']],
                                ['Post Max Size', $systemInfo['post_max_size']],
                            ];
                        @endphp
                        @foreach($sys as $s)
                            <div class="flex justify-between py-1 {{ !$loop->last ? 'border-b border-gray-100 dark:border-white/5' : '' }}">
                                <span class="text-gray-500">{{ $s[0] }}</span>
                                <span class="font-medium text-right">{{ $s[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
