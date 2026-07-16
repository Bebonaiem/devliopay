<?php

namespace App\Services\Gateways;

use App\Models\Setting;

class GatewayFactory
{
    private static array $gateways = [
        'stripe' => StripeGateway::class,
        'paypal' => PayPalGateway::class,
    ];

    public static function make(string $name): ?GatewayInterface
    {
        $class = self::$gateways[$name] ?? null;
        if (! $class || ! class_exists($class)) {
            return null;
        }

        $enabled = Setting::get("{$name}_enabled") ?: config("services.{$name}.enabled", false);
        if (! $enabled) {
            return null;
        }

        $instance = new $class;

        if (method_exists($instance, 'isConfigured') && ! $instance->isConfigured()) {
            return null;
        }

        return $instance;
    }

    public static function available(): array
    {
        $gateways = [];
        foreach (self::$gateways as $name => $class) {
            $enabled = Setting::get("{$name}_enabled") ?: config("services.{$name}.enabled", false);
            if ($enabled) {
                $instance = new $class;
                if (method_exists($instance, 'isConfigured') && ! $instance->isConfigured()) {
                    continue;
                }
                $gateways[$name] = $instance;
            }
        }

        return $gateways;
    }

    public static function getAll(): array
    {
        return array_keys(self::$gateways);
    }

    public static function getChoices(): array
    {
        $choices = [];
        foreach (self::available() as $name => $gateway) {
            $choices[$name] = $gateway->getDisplayName();
        }

        return $choices;
    }
}
