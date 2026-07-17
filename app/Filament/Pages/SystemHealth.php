<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Service;
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

    public array $recentActivity = [];

    public array $systemInfo = [];

    public function mount(): void
    {
        $this->loadStats();
        $this->loadRecentActivity();
        $this->loadSystemInfo();
    }

    private function loadStats(): void
    {
        $this->stats = [
            'total_users' => User::count(),
            'total_services' => Service::count(),
            'active_services' => Service::where('status', 'active')->count(),
            'suspended_services' => Service::where('status', 'suspended')->count(),
            'total_revenue' => Transaction::where('status', 'completed')->sum('amount'),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('status', 'pending')->where('due_at', '<', now())->count(),
            'revenue_this_month' => Transaction::where('status', 'completed')
                ->where('completed_at', '>=', now()->startOfMonth())
                ->sum('amount'),
            'revenue_last_month' => Transaction::where('status', 'completed')
                ->where('completed_at', '>=', now()->subMonth()->startOfMonth())
                ->where('completed_at', '<', now()->startOfMonth())
                ->sum('amount'),
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'new_orders_this_month' => Order::where('created_at', '>=', now()->startOfMonth())->count(),
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
                    'number' => $t->number,
                    'user' => $t->user->name ?? 'Unknown',
                    'subject' => $t->subject,
                    'priority' => $t->priority,
                    'date' => $t->created_at->diffForHumans(),
                ]),
        ];
    }

    private function loadSystemInfo(): void
    {
        $this->systemInfo = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'database_driver' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'disk_usage' => $this->getDiskUsage(),
            'extensions' => $this->checkExtensions(),
        ];
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

        return round($bytes, 2).' '.$units[$i];
    }

    private function checkExtensions(): array
    {
        $required = ['openssl', 'curl', 'mbstring', 'json', 'xml', 'pdo', 'tokenizer', 'bcmath', 'intl', 'gd'];
        $results = [];

        foreach ($required as $ext) {
            $results[$ext] = extension_loaded($ext);
        }

        return $results;
    }
}
