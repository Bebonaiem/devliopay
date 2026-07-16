<?php

namespace App\Services\Servers;

class ServerFactory
{
    private static array $servers = [
        'pterodactyl' => PterodactylServer::class,
    ];

    public static function make(?string $name): ?ServerInterface
    {
        $class = self::$servers[$name] ?? null;
        if ($class && class_exists($class)) {
            return new $class;
        }

        return null;
    }

    public static function available(): array
    {
        return array_keys(self::$servers);
    }

    public static function getAll(): array
    {
        return self::$servers;
    }
}
