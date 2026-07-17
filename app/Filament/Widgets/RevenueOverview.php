<?php

namespace App\Filament\Widgets;

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Order::whereIn('status', ['paid', 'completed'])->sum('total');
        $monthlyRevenue = Order::whereIn('status', ['paid', 'completed'])
            ->whereMonth('created_at', now()->month)
            ->sum('total');
        $totalUsers = User::count();
        $activeServices = Service::where('status', 'active')->count();
        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $totalOrders = Order::count();

        $symbol = Currency::defaultSymbol();

        return [
            Stat::make('Total Revenue', $symbol.number_format($totalRevenue, 2))
                ->description('All time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Monthly Revenue', $symbol.number_format($monthlyRevenue, 2))
                ->description('This month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make('Total Users', number_format($totalUsers))
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Active Services', number_format($activeServices))
                ->description('Currently active')
                ->descriptionIcon('heroicon-m-server')
                ->color('success'),
            Stat::make('Pending Invoices', number_format($pendingInvoices))
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Total Orders', number_format($totalOrders))
                ->description('All orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
        ];
    }
}
