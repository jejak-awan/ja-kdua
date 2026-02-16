<?php

declare(strict_types=1);

namespace App\Console\Commands\Isp\Billing;

use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Billing\Invoice;
use App\Services\Isp\Network\RadiusService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IsolateOverdueCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:isolate-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically isolate customers with overdue unpaid invoices';

    /**
     * Execute the console command.
     */
    public function handle(RadiusService $radius): int
    {
        $this->info('Starting automated billing isolation check...');

        // Find customers who are active but have overdue unpaid invoices
        // We join with users because invoices are linked to user_id
        $overdueCustomers = Customer::where('status', 'active')
            ->whereHas('user.invoices', function ($query) {
                $query->where('status', 'unpaid')
                    ->where('due_date', '<', Carbon::now());
            })
            ->get();

        if ($overdueCustomers->isEmpty()) {
            $this->info('No overdue active customers found.');
            return 0;
        }

        $this->info("Found {$overdueCustomers->count()} customers to isolate.");

        foreach ($overdueCustomers as $customer) {
            $name = $customer->user->name ?? 'Unknown';
            $this->info("Isolating customer: {$name} ({$customer->mikrotik_login})");

            try {
                // 1. Update Database Status
                $customer->update(['status' => 'isolated']);

                // 2. Sync to Radius with Suspended Profile
                if ($customer->mikrotik_login) {
                    $radius->syncUser($customer->mikrotik_login, (string) $customer->mikrotik_password, [
                        'Mikrotik-Group' => 'suspended',
                        'Filter-Id' => 'suspended',
                    ]);

                    // 3. Trigger immediate disconnect (CoA)
                    $radius->sendDisconnectRequest($customer);
                }

                Log::info("Automated Isolation: Customer #{$customer->id} ({$customer->mikrotik_login}) isolated due to overdue invoices.");
            } catch (\Exception $e) {
                $this->error("Failed to isolate customer #{$customer->id}: " . $e->getMessage());
                Log::error("Automated Isolation Error for Customer #{$customer->id}: " . $e->getMessage());
            }
        }

        $this->info('Automated isolation completed.');
        return 0;
    }
}
