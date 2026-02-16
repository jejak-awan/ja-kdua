<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Isp\Network\ZtpDiscoveryService;
use Illuminate\Console\Command;

class DiscoverOnusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:discover-onus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan all active OLTs for unconfigured ONUs and notify NOC';

    /**
     * Execute the console command.
     */
    public function handle(ZtpDiscoveryService $discoveryService): int
    {
        $this->info('Starting network-wide ONU discovery...');
        
        $discoveryService->scanAllOlts();
        
        $this->info('Discovery process completed successfully.');
        
        return Command::SUCCESS;
    }
}
