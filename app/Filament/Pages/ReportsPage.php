<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ReportsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Reports & Statistics';

    protected static string $view = 'filament.pages.reports';

    public ?array $data = [];

    public array $stats = [];

    public string $currencySymbol = '$';

    public array $revenueByMonth = [];

    public array $topProducts = [];

    public array $recentOrders = [];

    public string $periodLabel = 'Last 30 Days';

    public int $maxMonthRevenue = 0;

    public function mount(): void
    {
        $this->currencySymbol = Setting::get('default_currency_symbol', '$');
        $this->data = [
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ];

        $this->loadStats();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->required(),
                        Forms\Components\Actions\ActionContainer::make(
                            Forms\Components\Actions\Action::make('filter')
                                ->label('Apply Filter')
                                ->icon('heroicon-m-funnel')
                                ->color('primary')
                                ->action(fn () => $this->loadStats())
                        ),
                    ]),
            ])
            ->statePath('data');
    }

    public function setPreset(string $preset): void
    {
        $start = match ($preset) {
            'today' => now()->format('Y-m-d'),
            'yesterday' => now()->subDay()->format('Y-m-d'),
            'week' => now()->startOfWeek()->format('Y-m-d'),
            'month' => now()->startOfMonth()->format('Y-m-d'),
            'quarter' => now()->startOfQuarter()->format('Y-m-d'),
            'year' => now()->startOfYear()->format('Y-m-d'),
            'last30' => now()->subDays(30)->format('Y-m-d'),
            'last90' => now()->subDays(90)->format('Y-m-d'),
            default => now()->subDays(30)->format('Y-m-d'),
        };

        $end = match ($preset) {
            'yesterday' => now()->subDay()->format('Y-m-d'),
            default => now()->format('Y-m-d'),
        };

        $this->data['start_date'] = $start;
        $this->data['end_date'] = $end;

        $this->periodLabel = match ($preset) {
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'week' => 'This Week',
            'month' => 'This Month',
            'quarter' => 'This Quarter',
            'year' => 'This Year',
            'last30' => 'Last 30 Days',
            'last90' => 'Last 90 Days',
            default => 'Custom Range',
        };

        $this->loadStats();
    }

    public function loadStats(): void
    {
        $data = $this->data;
        $startDate = $data['start_date'] ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $data['end_date'] ?? now()->format('Y-m-d');

        $this->stats['total_revenue'] = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('total');

        $this->stats['monthly_revenue'] = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $this->stats['total_orders'] = Order::whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $this->stats['total_services'] = Service::count();

        $this->stats['total_users'] = User::count();

        $this->stats['new_users'] = User::whereBetween('created_at', [$startDate, $endDate])->count();

        $this->stats['active_services'] = Service::where('status', 'active')->count();

        $this->stats['pending_invoices'] = Invoice::where('status', 'pending')->count();

        $this->stats['overdue_invoices'] = Invoice::where('status', 'pending')
            ->where('due_at', '<', now())
            ->count();

        $this->stats['avg_invoice_value'] = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->avg('total');

        $this->stats['conversion_rate'] = $this->stats['total_users'] > 0
            ? round(($this->stats['total_services'] / $this->stats['total_users']) * 100, 1)
            : 0;

        $this->revenueByMonth = [];
        $maxRevenue = 0;
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereMonth('paid_at', $month->month)
                ->whereYear('paid_at', $month->year)
                ->sum('total');
            $this->revenueByMonth[] = [
                'month' => $month->format('M'),
                'year' => $month->format('Y'),
                'revenue' => number_format($revenue, 2),
                'raw' => $revenue,
            ];
            $maxRevenue = max($maxRevenue, $revenue);
        }
        $this->maxMonthRevenue = max($maxRevenue, 1);

        $this->topProducts = OrderItem::select('product_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('order_count')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->product?->name ?? 'Unknown',
                'order_count' => $item->order_count,
                'total_revenue' => number_format($item->total_revenue, 2),
                'raw_revenue' => $item->total_revenue,
            ])
            ->toArray();

        $maxProductRevenue = collect($this->topProducts)->max('raw_revenue') ?: 1;
        foreach ($this->topProducts as &$product) {
            $product['bar_width'] = round(($product['raw_revenue'] / $maxProductRevenue) * 100);
        }

        $this->recentOrders = Order::with('user')
            ->whereIn('status', ['paid', 'completed'])
            ->orderByDesc('created_at')
            ->limit(5)
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

    public function getTotalRevenue(): string
    {
        return number_format($this->stats['total_revenue'] ?? 0, 2);
    }

    public function getMonthlyRevenue(): string
    {
        return number_format($this->stats['monthly_revenue'] ?? 0, 2);
    }

    public function getAvgInvoiceValue(): string
    {
        return number_format($this->stats['avg_invoice_value'] ?? 0, 2);
    }
}
