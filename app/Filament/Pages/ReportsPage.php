<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
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

    protected static ?string $navigationGroup = null;

    protected static ?string $navigationLabel = 'Reports';

    protected static ?string $title = 'Reports & Statistics';

    protected static string $view = 'filament.pages.reports';

    public ?array $data = [];

    public array $stats = [];

    public array $revenueByMonth = [];

    public array $topProducts = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_date' => now()->subDays(30)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]);

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
                                ->label('Filter')
                                ->icon('heroicon-m-funnel')
                                ->color('primary')
                                ->action(fn () => $this->loadStats())
                        ),
                    ]),
            ])
            ->statePath('data');
    }

    public function loadStats(): void
    {
        $data = $this->form->getState();
        $startDate = $data['start_date'] ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $data['end_date'] ?? now()->format('Y-m-d');

        $this->stats['total_revenue'] = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('total');

        $this->stats['monthly_revenue'] = Invoice::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');

        $this->stats['total_orders'] = Order::count();

        $this->stats['total_services'] = Service::count();

        $this->stats['total_users'] = User::count();

        $this->stats['active_services'] = Service::where('status', 'active')->count();

        $this->stats['pending_invoices'] = Invoice::where('status', 'pending')->count();

        $this->stats['avg_invoice_value'] = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->avg('total');

        $this->revenueByMonth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Invoice::where('status', 'paid')
                ->whereMonth('paid_at', $month->month)
                ->whereYear('paid_at', $month->year)
                ->sum('total');
            $this->revenueByMonth[] = [
                'month' => $month->format('M Y'),
                'revenue' => number_format($revenue, 2),
                'raw' => $revenue,
            ];
        }

        $this->topProducts = OrderItem::select('product_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->product?->name ?? 'Unknown',
                'order_count' => $item->order_count,
                'total_revenue' => number_format($item->total_revenue, 2),
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
