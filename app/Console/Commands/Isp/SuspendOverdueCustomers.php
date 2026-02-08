<?php

namespace App\Console\Commands\Isp;

use App\Services\Isp\BillingService;
use Illuminate\Console\Command;

class SuspendOverdueCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:suspend-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Suspend customers with unpaid overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService): void
    {
        $this->info('Starting overdue suspension check...');
        $count = $billingService->suspendOverdueCustomers();
        $this->info("Successfully suspended {$count} overdue customers.");
    }
}
