<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TicketThread;
use App\Models\Transaction;
use App\Models\User;
use Filament\Pages\Page;

class SystemHealth extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'System Health';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'System Health';

    protected static string $view = 'filament.pages.system-health';

    public array $stats = [];

    public string $currencySymbol = '$';

    public array $recentActivity = [];

    public array $systemInfo = [];

    public array $healthChecks = [];

    public function mount(): void
    {
        $this->currencySymbol = Setting::get('default_currency_symbol', '$');
        $this->loadStats();
        $this->loadRecentActivity();
        $this->loadSystemInfo();
        $this->loadHealthChecks();
    }

    private function loadStats(): void
    {
        $activeServices = Service::where('status', 'active')->count();
        $suspendedServices = Service::where('status', 'suspended')->count();
        $pendingServices = Service::where('status', 'pending')->count();
        $terminatedServices = Service::where('status', 'terminated')->count();

        $pendingInvoices = Invoice::where('status', 'pending');
        $overdueInvoices = (clone $pendingInvoices)->where('due_at', '<', now())->count();
        $pendingInvoicesCount = (clone $pendingInvoices)->count();

        $revenueThisMonth = Transaction::where('status', 'completed')
            ->where('completed_at', '>=', now()->startOfMonth())
            ->sum('amount');

        $revenueLastMonth = Transaction::where('status', 'completed')
            ->where('completed_at', '>=', now()->subMonth()->startOfMonth())
            ->where('completed_at', '<', now()->startOfMonth())
            ->sum('amount');

        $totalRevenue = Transaction::where('status', 'completed')->sum('amount');

        $this->stats = [
            'total_users' => User::count(),
            'total_services' => Service::count(),
            'active_services' => $activeServices,
            'suspended_services' => $suspendedServices,
            'pending_services' => $pendingServices,
            'terminated_services' => $terminatedServices,
            'total_revenue' => $totalRevenue,
            'pending_invoices' => $pendingInvoicesCount,
            'overdue_invoices' => $overdueInvoices,
            'revenue_this_month' => $revenueThisMonth,
            'revenue_last_month' => $revenueLastMonth,
            'revenue_change' => $revenueLastMonth > 0
                ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
                : 0,
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'new_orders_this_month' => Order::where('created_at', '>=', now()->startOfMonth())->count(),
            'open_tickets' => TicketThread::whereIn('status', ['open', 'customer_reply'])->count(),
        ];
    }

    private function loadRecentActivity(): void
    {
        $this->recentActivity = [
            'recent_orders' => Order::with('user')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'number' => $o->number,
                    'user' => $o->user->name ?? 'Unknown',
                    'total' => $o->total,
                    'status' => $o->status,
                    'date' => $o->created_at->diffForHumans(),
                ]),
            'recent_tickets' => TicketThread::with('user')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'number' => $t->number,
                    'user' => $t->user->name ?? 'Unknown',
                    'subject' => $t->subject,
                    'priority' => $t->priority,
                    'status' => $t->status,
                    'date' => $t->created_at->diffForHumans(),
                ]),
            'recent_transactions' => Transaction::with('user')
                ->where('status', 'completed')
                ->orderByDesc('completed_at')
                ->limit(5)
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'user' => $t->user->name ?? 'Unknown',
                    'amount' => $t->amount,
                    'gateway' => $t->gateway,
                    'date' => $t->completed_at?->diffForHumans() ?? $t->created_at->diffForHumans(),
                ]),
        ];
    }

    private function loadSystemInfo(): void
    {
        $this->systemInfo = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'app_url' => config('app.url'),
            'app_name' => config('app.name'),
            'timezone' => config('app.timezone'),
            'database_driver' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
            'mail_driver' => config('mail.default'),
            'debug_mode' => config('app.debug'),
            'memory_limit' => ini_get('memory_limit'),
            'max_upload_size' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'post_max_size' => ini_get('post_max_size'),
            'disk_usage' => $this->getDiskUsage(),
            'extensions' => $this->checkExtensions(),
        ];
    }

    private function loadHealthChecks(): void
    {
        $this->healthChecks = [
            'application' => [
                'label' => 'Application',
                'status' => $this->checkApplicationHealth(),
                'checks' => [
                    ['label' => 'Environment', 'ok' => !app()->isDownForMaintenance(), 'info' => app()->isDownForMaintenance() ? 'Maintenance mode on' : app()->environment()],
                    ['label' => 'Debug Mode', 'ok' => !config('app.debug'), 'info' => config('app.debug') ? 'Enabled (' . config('app.env') . ')' : 'Disabled'],
                    ['label' => 'APP_URL', 'ok' => !empty(config('app.url')), 'info' => config('app.url') ?: 'Not set'],
                ],
            ],
            'database' => [
                'label' => 'Database',
                'status' => $this->checkDatabaseHealth(),
                'checks' => [
                    ['label' => 'Connection', 'ok' => $this->checkDatabaseConnection(), 'info' => config('database.default')],
                    ['label' => 'Migrations', 'ok' => $this->checkMigrations(), 'info' => $this->getMigrationStatus()],
                ],
            ],
            'cache' => [
                'label' => 'Cache',
                'status' => $this->checkCacheHealth(),
                'checks' => [
                    ['label' => 'Driver', 'ok' => true, 'info' => config('cache.default')],
                    ['label' => 'Config Cached', 'ok' => app()->configurationIsCached(), 'info' => app()->configurationIsCached() ? 'Cached' : 'Not cached'],
                    ['label' => 'Routes Cached', 'ok' => app()->routesAreCached(), 'info' => app()->routesAreCached() ? 'Cached' : 'Not cached'],
                    ['label' => 'Events Cached', 'ok' => app()->eventsAreCached(), 'info' => app()->eventsAreCached() ? 'Cached' : 'Not cached'],
                ],
            ],
            'queue' => [
                'label' => 'Queue',
                'status' => $this->checkQueueHealth(),
                'checks' => [
                    ['label' => 'Driver', 'ok' => config('queue.default') !== 'sync', 'info' => config('queue.default')],
                    ['label' => 'Failed Jobs', 'ok' => $this->checkFailedJobs(), 'info' => $this->getFailedJobsCount()],
                ],
            ],
            'mail' => [
                'label' => 'Mail',
                'status' => $this->checkMailHealth(),
                'checks' => [
                    ['label' => 'Driver', 'ok' => config('mail.default') !== 'log' && !empty(config('mail.default')), 'info' => config('mail.default')],
                    ['label' => 'From Address', 'ok' => !empty(config('mail.from.address')), 'info' => config('mail.from.address') ?: 'Not set'],
                ],
            ],
            'storage' => [
                'label' => 'Storage',
                'status' => $this->checkStorageHealth(),
                'checks' => [
                    ['label' => 'Disk Space', 'ok' => $this->systemInfo['disk_usage']['percent'] < 90, 'info' => $this->systemInfo['disk_usage']['percent'] . '% used'],
                    ['label' => 'Storage Link', 'ok' => is_dir(public_path('storage')), 'info' => is_dir(public_path('storage')) ? 'Linked' : 'Not linked'],
                ],
            ],
        ];
    }

    private function checkApplicationHealth(): string
    {
        if (app()->isDownForMaintenance()) return 'danger';
        if (config('app.debug') && config('app.env') !== 'local') return 'warning';
        return 'success';
    }

    private function checkDatabaseHealth(): string
    {
        if (!$this->checkDatabaseConnection()) return 'danger';
        if (!$this->checkMigrations()) return 'warning';
        return 'success';
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    private function checkMigrations(): bool
    {
        try {
            $migrations = \DB::table('migrations')->count();
            return $migrations > 0;
        } catch (\Exception) {
            return false;
        }
    }

    private function getMigrationStatus(): string
    {
        try {
            $count = \DB::table('migrations')->count();
            return $count . ' run';
        } catch (\Exception) {
            return 'Not run';
        }
    }

    private function checkCacheHealth(): string
    {
        if (config('cache.default') === 'array') return 'warning';
        return 'success';
    }

    private function checkQueueHealth(): string
    {
        if (config('queue.default') === 'sync') return 'warning';
        return 'success';
    }

    private function checkFailedJobs(): bool
    {
        try {
            return \DB::table('failed_jobs')->count() === 0;
        } catch (\Exception) {
            return true;
        }
    }

    private function getFailedJobsCount(): string
    {
        try {
            $count = \DB::table('failed_jobs')->count();
            return $count > 0 ? $count . ' failed' : 'None';
        } catch (\Exception) {
            return 'Unknown';
        }
    }

    private function checkMailHealth(): string
    {
        if (config('mail.default') === 'log' || empty(config('mail.default'))) return 'warning';
        if (empty(config('mail.from.address'))) return 'warning';
        return 'success';
    }

    private function checkStorageHealth(): string
    {
        if ($this->systemInfo['disk_usage']['percent'] >= 90) return 'danger';
        if ($this->systemInfo['disk_usage']['percent'] >= 80) return 'warning';
        return 'success';
    }

    private function getDiskUsage(): array
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;

        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percent' => round(($used / $total) * 100, 1),
            'percent_free' => round(($free / $total) * 100, 1),
            'total_bytes' => $total,
            'used_bytes' => $used,
            'free_bytes' => $free,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function checkExtensions(): array
    {
        $required = ['openssl', 'curl', 'mbstring', 'json', 'xml', 'pdo', 'tokenizer', 'bcmath', 'intl', 'gd', 'fileinfo'];
        $results = [];
        foreach ($required as $ext) {
            $results[$ext] = extension_loaded($ext);
        }
        return $results;
    }
}
