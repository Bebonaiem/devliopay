<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $activeServices = $user->services()->where('status', 'active')->count();
        $pendingInvoices = $user->invoices()->where('status', 'pending')->count();
        $openTickets = $user->tickets()->where('status', 'open')->count();
        $totalSpent = $user->transactions()->where('status', 'completed')->sum('amount');

        $recentServices = $user->services()
            ->with('product')
            ->latest()
            ->limit(5)
            ->get();

        $recentInvoices = $user->invoices()
            ->latest()
            ->limit(5)
            ->get();

        // Monthly spending data for chart (last 12 months)
        $monthlySpending = $user->transactions()
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw("strftime('%Y-%m', completed_at) as month"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill in missing months
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $chartData[$month] = $monthlySpending[$month] ?? 0;
        }

        // Service breakdown
        $serviceBreakdown = $user->services()
            ->with('product.category')
            ->where('status', 'active')
            ->get()
            ->groupBy(fn ($s) => $s->product?->category->name ?? 'Other')
            ->map(fn ($services) => $services->count())
            ->toArray();

        // Upcoming renewals
        $upcomingRenewals = $user->services()
            ->with('product')
            ->where('status', 'active')
            ->where('next_billing_at', '<=', now()->addDays(30))
            ->where('next_billing_at', '>', now())
            ->orderBy('next_billing_at')
            ->limit(5)
            ->get();

        // Recent activity
        $recentActivity = $user->services()
            ->where('updated_at', '>=', now()->subDays(7))
            ->with('product')
            ->latest()
            ->limit(5)
            ->get();

        return view('client.dashboard', compact(
            'activeServices',
            'pendingInvoices',
            'openTickets',
            'totalSpent',
            'recentServices',
            'recentInvoices',
            'chartData',
            'serviceBreakdown',
            'upcomingRenewals',
            'recentActivity'
        ));
    }
}
