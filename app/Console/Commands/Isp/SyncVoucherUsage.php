<?php

namespace App\Console\Commands\Isp;

use App\Services\Isp\Billing\VoucherService;
use Illuminate\Console\Command;

class SyncVoucherUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:sync-voucher-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync voucher usage status from RADIUS accounting data';

    /**
     * Execute the console command.
     */
    public function handle(VoucherService $voucherService): int
    {
        $this->info('Starting voucher usage synchronization...');

        $count = $voucherService->syncUsageWithRadius();

        $this->info("Successfully synced {$count} vouchers.");

        return 0;
    }
}
