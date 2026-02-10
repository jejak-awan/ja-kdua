<?php

namespace App\Console\Commands\Isp;

use App\Models\Isp\Customer;
use App\Services\Isp\RadiusIntegration;
use Illuminate\Console\Command;

class CheckFup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:check-fup {customer_id? : Optional customer ID to check specific user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync customer usage from RADIUS and enforce Fair Usage Policy (FUP) throttling';

    /**
     * Execute the console command.
     */
    public function handle(RadiusIntegration $radius): int
    {
        $customerId = $this->argument('customer_id');

        $query = Customer::where('status', 'active')
            ->whereNotNull('mikrotik_login')
            ->whereHas('plan', function ($q) {
                $q->where('fup_enabled', true);
            });

        if ($customerId) {
            $query->where('id', $customerId);
        }

        $customers = $query->get();

        $this->info('Checking FUP for '.$customers->count().' active customers...');

        foreach ($customers as $customer) {
            $this->comment("Processing {$customer->mikrotik_login}...");

            // 1. Sync usage from radacct
            $radius->syncUsageData($customer);

            // 2. Refresh customer model to get latest data
            $customer->refresh();

            // 3. Apply FUP if needed
            $applied = $radius->applyFup($customer);

            if ($applied) {
                $this->warn("FUP Applied/Updated for {$customer->mikrotik_login}");
            }
        }

        $this->info('FUP check completed.');

        return 0;
    }
}
