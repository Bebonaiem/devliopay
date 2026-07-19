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

        $this->info('Billing processing complete.');

        return self::SUCCESS;
    }
}
