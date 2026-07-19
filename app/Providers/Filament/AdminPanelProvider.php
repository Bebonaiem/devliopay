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
use Filament\Navigation\MenuItem;
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
            ->userMenuItems([
                MenuItem::make()
                    ->label('Client Area')
                    ->url('/client')
                    ->icon('heroicon-o-arrow-top-right-on-square'),
            ])
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
