<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Service;
use App\Models\TicketThread;
use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public array $stats = [];

    public array $recentOrders = [];

    public array $recentTickets = [];

    public array $recentServices = [];

    public array $revenueData = [];

    public function mount(): void
    {
        parent::mount();

        $this->loadStats();
        $this->loadRecentOrders();
        $this->loadRecentTickets();
        $this->loadRecentServices();
        $this->loadRevenueChart();
    }

    private function loadStats(): void
    {
        $this->stats = [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'total_services' => Service::count(),
            'active_services' => Service::where('status', 'active')->count(),
            'suspended_services' => Service::where('status', 'suspended')->count(),
            'pending_services' => Service::where('status', 'pending')->count(),
            'total_revenue' => Order::whereIn('status', ['paid', 'completed'])->sum('total'),
            'revenue_today' => Order::whereIn('status', ['paid', 'completed'])->whereDate('created_at', today())->sum('total'),
            'revenue_month' => Order::whereIn('status', ['paid', 'completed'])->where('created_at', '>=', now()->startOfMonth())->sum('total'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('status', 'pending')->where('due_at', '<', now())->count(),
            'total_tickets' => TicketThread::count(),
            'open_tickets' => TicketThread::where('status', 'open')->count(),
            'unanswered_tickets' => TicketThread::where('status', 'open')->where('updated_at', '<', now()->subHours(24))->count(),
        ];
    }

    private function loadRecentOrders(): void
    {
        $this->recentOrders = Order::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'number' => $order->number,
                'user' => $order->user->name ?? 'Unknown',
                'total' => number_format($order->total, 2),
                'status' => $order->status,
                'date' => $order->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    private function loadRecentTickets(): void
    {
        $this->recentTickets = TicketThread::with('user', 'department')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($ticket) => [
                'id' => $ticket->id,
                'number' => $ticket->number,
                'user' => $ticket->user->name ?? 'Unknown',
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'department' => $ticket->department->name ?? '-',
                'date' => $ticket->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    private function loadRecentServices(): void
    {
        $this->recentServices = Service::with('user', 'product')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($service) => [
                'id' => $service->id,
                'user' => $service->user->name ?? 'Unknown',
                'product' => $service->product->name ?? 'Unknown',
                'status' => $service->status,
                'date' => $service->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    private function loadRevenueChart(): void
    {
        $days = 30;
        $data = Order::whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('revenue', 'date')
            ->toArray();

        $labels = [];
        $values = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('M d');
            $values[] = $data[$date] ?? 0;
        }

        $this->revenueData = [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
