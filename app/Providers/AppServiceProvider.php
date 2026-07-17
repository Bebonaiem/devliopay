<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\TicketThread;
use App\Policies\TicketThreadPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.url') && str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        Gate::policy(TicketThread::class, TicketThreadPolicy::class);

        View::composer('*', function ($view) {
            $stripePublishable = Setting::get('stripe_publishable_key') ?: (config('services.stripe.publishable_key') ?? '');
            $view->with('stripePublishableKey', $stripePublishable);

            $unreadCount = 0;
            if (Auth::check()) {
                $unreadCount = Auth::user()->unreadNotifications()->count();
            }
            $view->with('unreadNotificationCount', $unreadCount);

            $cart = session()->get('cart', []);
            $cartCount = is_array($cart) ? count($cart) : 0;
            $view->with('cartCount', $cartCount);
        });
    }
}
