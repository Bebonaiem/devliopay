<?php

namespace App\Services\Servers;

use App\Models\Service;

interface ServerInterface
{
    public function createServer(Service $service, array $settings, array $properties): array;

    public function suspendServer(Service $service, array $settings, array $properties): array;

    public function unsuspendServer(Service $service, array $settings, array $properties): array;

    public function terminateServer(Service $service, array $settings, array $properties): array;

    public function upgradeServer(Service $service, array $settings, array $properties): array;

    public function reinstallServer(Service $service, array $settings, array $properties): array;

    public function getServerInfo(Service $service): array;

    public function getServerResources(Service $service): array;

    public function getPanelUrl(Service $service): ?string;

    public function getProductConfig(array $values = []): array;

    public function getCheckoutConfig($product, array $values = [], array $settings = []): array;

    public function getActions(Service $service, array $settings, array $properties): array;

    public function getAllocations(Service $service): array;
}
