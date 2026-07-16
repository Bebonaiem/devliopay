<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Services\ServerProvisioningService;
use Illuminate\Console\Command;

class ProvisionService extends Command
{
    protected $signature = 'service:provision {serviceId}';

    protected $description = 'Provision a pending service';

    public function handle(): int
    {
        $service = Service::find($this->argument('serviceId'));

        if (! $service) {
            $this->error('Service not found.');

            return self::FAILURE;
        }

        if ($service->status !== 'pending') {
            $this->error('Service is not pending.');

            return self::FAILURE;
        }

        $this->info("Provisioning service {$service->uuid}...");

        $provisioning = new ServerProvisioningService;
        $result = $provisioning->provision($service);

        if ($result['success']) {
            $this->info('Service provisioned successfully.');

            return self::SUCCESS;
        }

        $this->error('Failed to provision service: '.($result['error'] ?? 'Unknown error'));

        return self::FAILURE;
    }
}
