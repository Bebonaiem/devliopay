<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Services\ActivityLogService;
use App\Services\BillingService;
use App\Services\ServerProvisioningService;
use Illuminate\Console\Command;

class ProcessBilling extends Command
{
    protected $signature = 'billing:process';

    protected $description = 'Process billing - generate invoices, handle renewals, suspend/terminate overdue';

    public function handle(): int
    {
        $billingService = new BillingService;

        $this->info('Processing service renewals...');
        $billingService->processRenewals();

        $this->info('Processing overdue invoices...');
        $billingService->processOverdueInvoices();

        $this->info('Checking for suspended services to terminate...');
        $this->terminateLongOverdue();

        $this->info('Billing processing complete.');

        return self::SUCCESS;
    }

    private function terminateLongOverdue(): void
    {
        $overdueServices = Service::where('status', 'suspended')
            ->where('suspended_at', '<=', now()->subDays(30))
            ->get();

        foreach ($overdueServices as $service) {
            $provisioning = new ServerProvisioningService;
            $result = $provisioning->terminate($service);

            if ($result['success']) {
                $service->update([
                    'status' => 'terminated',
                    'terminated_at' => now(),
                ]);
                ActivityLogService::log('service_terminated', $service, 'Service auto-terminated after 30 days overdue');
                $this->info("Terminated service #{$service->id}");
            }
        }
    }
}
