<?php

declare(strict_types=1);

namespace App\Console\Commands\Isp;

use App\Jobs\Isp\DriftDetectionJob;
use App\Jobs\Isp\BruteforceMonitorJob;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Console\Command;

class MaintenancePollerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:maintenance-poll {--type=all : all, drift, bforce}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run maintenance and security audits for all active ISP routers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = (string) $this->option('type');
        $this->info("Starting ISP Maintenance Poller (Type: {$type})...");
        
        $routers = ServiceNode::where('type', 'Router')->get();
        
        $this->info("Found {$routers->count()} routers to audit.");

        foreach ($routers as $router) {
            if ($type === 'all' || $type === 'drift') {
                $this->line("Dispatching drift detection for {$router->name}...");
                \App\Jobs\Isp\DriftDetectionJob::dispatch($router);
            }

            if ($type === 'all' || $type === 'bforce') {
                $this->line("Dispatching bruteforce monitor for {$router->name}...");
                \App\Jobs\Isp\BruteforceMonitorJob::dispatch($router);
            }

            if ($type === 'all' || $type === 'revenue') {
                $this->line("Dispatching revenue assurance for {$router->name}...");
                \App\Jobs\Isp\RevenueAssuranceJob::dispatch($router);
            }
        }

        $this->info('All maintenance jobs dispatched.');
        
        return self::SUCCESS;
    }
}
