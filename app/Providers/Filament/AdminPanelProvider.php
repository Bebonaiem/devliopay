<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\DevlioPayInfoWidget;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\RevenueOverview;
use App\Models\Setting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName(Setting::get('company_name', config('app.name', 'DevlioPay')))
            ->brandLogo(Setting::get('company_logo') ? asset('storage/'.Setting::get('company_logo')) : asset('logo.svg'))
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->darkMode(isForced: true)
            ->navigationGroups([
                'Users',
                'Billing',
                'Services',
                'Store',
                'Support',
                'Content',
                'Reports',
                'Configuration',
                'System',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                DevlioPayInfoWidget::class,
                RevenueOverview::class,
                RevenueChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::topbar.end',
                fn () => '<a href="/client" class="fi-btn fi-btn-size-sm inline-flex items-center gap-2 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 transition-colors fi-color-primary" title="Client Dashboard"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg> Client Area</a>'
            )
            ->renderHook(
                'head',
                fn () => (Setting::get('company_favicon')
                    ? '<link rel="icon" type="image/x-icon" href="/storage/' . ltrim(Setting::get('company_favicon'), '/') . '">'
                    : '<link rel="icon" type="image/x-icon" href="/favicon.ico">')
                . <<<'HTML'
                <style>
                    button, .fi-btn, .fi-action button, a[class*="bg-primary"], a[class*="bg-danger"], a[class*="bg-success"], a[class*="bg-warning"] {
                        border: 1px solid rgba(255, 255, 255, 0.1) !important;
                    }
                </style>
                HTML
            );
    }
}
