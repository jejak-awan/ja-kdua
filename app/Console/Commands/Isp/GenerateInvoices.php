<?php

namespace App\Console\Commands\Isp;

use App\Services\Isp\Billing\BillingService;
use Illuminate\Console\Command;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:generate-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for customers whose billing cycle starts today';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService): void
    {
        $this->info('Starting invoice generation...');
        $count = $billingService->generateInvoicesForToday();
        $this->info("Successfully generated {$count} invoices.");
    }
}
