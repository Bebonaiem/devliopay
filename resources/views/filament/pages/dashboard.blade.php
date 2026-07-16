<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="fi-card fi-stat-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/50">
                        <x-heroicon-o-users class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($stats['total_users']) }}</div>
                        <div class="text-xs text-gray-500">Users (+{{ $stats['new_users_today'] }} today)</div>
                    </div>
                </div>
            </div>

            <div class="fi-card fi-stat-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-success-100 dark:bg-success-900/50">
                        <x-heroicon-o-server class="w-5 h-5 text-success-600 dark:text-success-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($stats['active_services']) }}</div>
                        <div class="text-xs text-gray-500">Active Services</div>
                    </div>
                </div>
            </div>

            <div class="fi-card fi-stat-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-info-100 dark:bg-info-900/50">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-info-600 dark:text-info-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">${{ number_format($stats['revenue_month'], 2) }}</div>
                        <div class="text-xs text-gray-500">Revenue This Month</div>
                    </div>
                </div>
            </div>

            <div class="fi-card fi-stat-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-warning-100 dark:bg-warning-900/50">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($stats['total_orders']) }}</div>
                        <div class="text-xs text-gray-500">Total Orders</div>
                    </div>
                </div>
            </div>

            <div class="fi-card fi-stat-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $stats['overdue_invoices'] > 0 ? 'bg-danger-100 dark:bg-danger-900/50' : 'bg-gray-100 dark:bg-gray-800' }}">
                        <x-heroicon-o-document-text class="w-5 h-5 {{ $stats['overdue_invoices'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-600 dark:text-gray-400' }}" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($stats['pending_invoices']) }}</div>
                        <div class="text-xs text-gray-500">Pending Invoices @if($stats['overdue_invoices'] > 0)<span class="text-danger-500">({{ $stats['overdue_invoices'] }} overdue)</span>@endif</div>
                    </div>
                </div>
            </div>

            <div class="fi-card fi-stat-card bg-white dark:bg-white/5 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $stats['open_tickets'] > 0 ? 'bg-warning-100 dark:bg-warning-900/50' : 'bg-gray-100 dark:bg-gray-800' }}">
                        <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 {{ $stats['open_tickets'] > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-600 dark:text-gray-400' }}" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ number_format($stats['open_tickets']) }}</div>
                        <div class="text-xs text-gray-500">Open Tickets @if($stats['unanswered_tickets'] > 0)<span class="text-danger-500">({{ $stats['unanswered_tickets'] }} unanswered)</span>@endif</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue Chart --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-gray-400" />
                    <span>Revenue (Last 30 Days)</span>
                </div>
            </x-slot>

            <div class="h-72" wire:ignore id="revenue-chart">
                <canvas id="revenueChart"></canvas>
            </div>

            @script
            <script>
                const ctx = document.getElementById('revenueChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($revenueData['labels']),
                            datasets: [{
                                label: 'Revenue',
                                data: @json($revenueData['values']),
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 2,
                                pointHoverRadius: 5,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(156, 163, 175, 0.1)' },
                                    ticks: { color: '#9ca3af', callback: (v) => '$' + v.toLocaleString() },
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#9ca3af', maxTicksLimit: 10 },
                                },
                            },
                        },
                    });
                }
            </script>
            @endscript
        </x-filament::section>

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Recent Orders --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-shopping-cart class="w-5 h-5 text-gray-400" />
                            <span>Recent Orders</span>
                        </div>
                        <a href="{{ \App\Filament\Resources\OrderResource::getUrl('index') }}" class="text-xs text-primary-600 hover:text-primary-500">View All</a>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @forelse($recentOrders as $order)
                        <a href="{{ \App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $order['id']]) }}"
                           class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold">{{ $order['number'] }}</span>
                                    <x-filament::badge :color="match($order['status']) {
                                        'paid', 'completed' => 'success',
                                        'pending' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }" size="xs">{{ ucfirst($order['status']) }}</x-filament::badge>
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $order['user'] }} &middot; {{ $order['date'] }}</div>
                            </div>
                            <span class="text-sm font-semibold">${{ $order['total'] }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No orders yet</p>
                    @endforelse
                </div>
            </x-filament::section>

            {{-- Recent Tickets --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-gray-400" />
                            <span>Recent Tickets</span>
                        </div>
                        <a href="{{ \App\Filament\Resources\TicketThreadResource::getUrl('index') }}" class="text-xs text-primary-600 hover:text-primary-500">View All</a>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @forelse($recentTickets as $ticket)
                        <a href="{{ \App\Filament\Resources\TicketThreadResource::getUrl('edit', ['record' => $ticket['id']]) }}"
                           class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold">{{ $ticket['number'] }}</span>
                                    <x-filament::badge :color="match($ticket['status']) {
                                        'open' => 'warning',
                                        'answered' => 'success',
                                        'closed' => 'gray',
                                        default => 'gray',
                                    }" size="xs">{{ ucfirst($ticket['status']) }}</x-filament::badge>
                                    <x-filament::badge :color="match($ticket['priority']) {
                                        'urgent' => 'danger',
                                        'high' => 'warning',
                                        'medium' => 'info',
                                        'low' => 'gray',
                                        default => 'gray',
                                    }" size="xs">{{ ucfirst($ticket['priority']) }}</x-filament::badge>
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $ticket['subject'] }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $ticket['user'] }} &middot; {{ $ticket['department'] }} &middot; {{ $ticket['date'] }}</div>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No tickets yet</p>
                    @endforelse
                </div>
            </x-filament::section>

            {{-- Recent Services --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-server class="w-5 h-5 text-gray-400" />
                            <span>Recent Services</span>
                        </div>
                        <a href="{{ \App\Filament\Resources\ServiceResource::getUrl('index') }}" class="text-xs text-primary-600 hover:text-primary-500">View All</a>
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @forelse($recentServices as $service)
                        <a href="{{ \App\Filament\Resources\ServiceResource::getUrl('edit', ['record' => $service['id']]) }}"
                           class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold">{{ $service['product'] }}</span>
                                    <x-filament::badge :color="match($service['status']) {
                                        'active' => 'success',
                                        'suspended' => 'danger',
                                        'pending' => 'warning',
                                        'terminated' => 'gray',
                                        default => 'gray',
                                    }" size="xs">{{ ucfirst($service['status']) }}</x-filament::badge>
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $service['user'] }} &middot; {{ $service['date'] }}</div>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No services yet</p>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        {{-- Quick Links --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-link class="w-5 h-5 text-gray-400" />
                    <span>Quick Links</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
                <a href="{{ \App\Filament\Resources\UserResource::getUrl('index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <x-heroicon-o-users class="w-6 h-6 text-primary-500" />
                    <span class="text-xs font-medium">Users</span>
                </a>
                <a href="{{ \App\Filament\Resources\ProductResource::getUrl('index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <x-heroicon-o-cube class="w-6 h-6 text-primary-500" />
                    <span class="text-xs font-medium">Products</span>
                </a>
                <a href="{{ \App\Filament\Resources\OrderResource::getUrl('index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <x-heroicon-o-shopping-cart class="w-6 h-6 text-primary-500" />
                    <span class="text-xs font-medium">Orders</span>
                </a>
                <a href="{{ \App\Filament\Resources\InvoiceResource::getUrl('index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <x-heroicon-o-document-text class="w-6 h-6 text-primary-500" />
                    <span class="text-xs font-medium">Invoices</span>
                </a>
                <a href="{{ \App\Filament\Resources\ServiceResource::getUrl('index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <x-heroicon-o-server class="w-6 h-6 text-primary-500" />
                    <span class="text-xs font-medium">Services</span>
                </a>
                <a href="{{ \App\Filament\Resources\TicketThreadResource::getUrl('index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 text-primary-500" />
                    <span class="text-xs font-medium">Tickets</span>
                </a>
                <a href="{{ \App\Filament\Resources\AnnouncementResource::getUrl('index') }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <x-heroicon-o-megaphone class="w-6 h-6 text-primary-500" />
                    <span class="text-xs font-medium">Announcements</span>
                </a>
                <a href="{{ \App\Filament\Pages\SystemHealth::getUrl() }}"
                   class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <x-heroicon-o-heart class="w-6 h-6 text-primary-500" />
                    <span class="text-xs font-medium">System Health</span>
                </a>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
