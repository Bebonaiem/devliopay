<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'name',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    private static array $cache = [];

    private static array $envMap = [
        'stripe_enabled' => 'STRIPE_ENABLED',
        'stripe_secret_key' => 'STRIPE_SECRET_KEY',
        'stripe_restricted_key' => 'STRIPE_RESTRICTED_KEY',
        'stripe_publishable_key' => 'STRIPE_PUBLISHABLE_KEY',
        'stripe_webhook_secret' => 'STRIPE_WEBHOOK_SECRET',
        'paypal_enabled' => 'PAYPAL_ENABLED',
        'paypal_client_id' => 'PAYPAL_CLIENT_ID',
        'paypal_client_secret' => 'PAYPAL_CLIENT_SECRET',
        'paypal_mode' => 'PAYPAL_MODE',
        'pterodactyl_host' => 'PTERODACTYL_HOST',
        'pterodactyl_api_key' => 'PTERODACTYL_API_KEY',
    ];

    public static function get(string $name, mixed $default = null): mixed
    {
        if (isset(static::$cache[$name])) {
            return static::$cache[$name];
        }

        $setting = static::where('name', $name)->first();
        $value = $setting ? $setting->value : $default;

        static::$cache[$name] = $value;

        return $value;
    }

    public static function set(string $name, mixed $value): void
    {
        static::updateOrCreate(
            ['name' => $name],
            ['value' => $value]
        );

        unset(static::$cache[$name]);

        // Sync to .env if mapped
        if (isset(static::$envMap[$name])) {
            static::updateEnv(static::$envMap[$name], $value);
        }
    }

    public static function flushCache(): void
    {
        static::$cache = [];
    }

    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'name')
            ->toArray();
    }

    private static function updateEnv(string $key, mixed $value): void
    {
        $envFile = base_path('.env');
        if (! File::exists($envFile)) {
            return;
        }

        $content = File::get($envFile);
        $escapedValue = str_contains($value, ' ') ? '"'.$value.'"' : $value;
        $escapedKey = preg_quote($key, '/');

        // Key exists — update it
        if (preg_match("/^{$escapedKey}=.*/m", $content)) {
            $content = preg_replace("/^{$escapedKey}=.*/m", "{$key}={$escapedValue}", $content);
        }
        // Key doesn't exist — append it
        else {
            $content .= "\n{$key}={$escapedValue}\n";
        }

        File::put($envFile, $content);
    }
}
