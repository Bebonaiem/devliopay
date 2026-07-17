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
        $currentUrl = url('/');
        $savedUrl = config('app.url', '');

        if ($savedUrl && $currentUrl !== $savedUrl && !app()->runningInConsole()) {
            $this->syncUrlToEnv($currentUrl);
        }

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

            $view->with('companyName', Setting::get('company_name', config('app.name', 'DevlioPay')));
            $view->with('companyLogo', Setting::get('company_logo', '') ? '/storage/' . ltrim(Setting::get('company_logo', ''), '/') : '');
            $view->with('companyLogoDisplay', Setting::get('company_logo_display', 'name_only'));
            $view->with('companyFavicon', Setting::get('company_favicon', '') ? '/storage/' . ltrim(Setting::get('company_favicon', ''), '/') : '');
            $view->with('companyOgImage', Setting::get('company_og_image', '') ? '/storage/' . ltrim(Setting::get('company_og_image', ''), '/') : '');
            $view->with('companyEmail', Setting::get('company_email', ''));
            $view->with('companyUrl', Setting::get('company_url', ''));
            $view->with('companyPhone', Setting::get('company_phone', ''));
            $view->with('companyAddress', Setting::get('company_address', ''));
            $view->with('companyFooterText', Setting::get('company_footer_text', ''));
            $view->with('defaultCurrency', Setting::get('default_currency', 'USD'));
            $view->with('defaultCurrencySymbol', Setting::get('default_currency_symbol', '$'));
        });
    }

    private function syncUrlToEnv(string $url): void
    {
        $url = rtrim($url, '/');
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            return;
        }

        $content = file_get_contents($envFile);
        $domain = parse_url($url, PHP_URL_HOST) ?? '';

        if (str_contains($content, 'APP_URL=')) {
            $content = preg_replace("/^APP_URL=.*/m", "APP_URL={$url}", $content);
        } else {
            $content .= "\nAPP_URL={$url}";
        }

        if (str_contains($content, 'APP_DOMAIN=')) {
            $content = preg_replace("/^APP_DOMAIN=.*/m", "APP_DOMAIN={$domain}", $content);
        } else {
            $content .= "\nAPP_DOMAIN={$domain}";
        }

        file_put_contents($envFile, $content);

        putenv("APP_URL={$url}");
        $_ENV['APP_URL'] = $url;
        config(['app.url' => $url]);

        putenv("APP_DOMAIN={$domain}");
        $_ENV['APP_DOMAIN'] = $domain;
        config(['app.domain' => $domain]);
    }
}
