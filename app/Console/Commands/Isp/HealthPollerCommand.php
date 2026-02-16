<?php

declare(strict_types=1);

namespace App\Console\Commands\Isp;

use App\Jobs\Isp\RouterHealthJob;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Console\Command;

class HealthPollerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:health-poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll health and metrics for all active ISP routers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting ISP Health Poller...');
        
        $routers = ServiceNode::where('type', 'Router')->get();
        
        $this->info("Found {$routers->count()} routers to poll.");

        foreach ($routers as $router) {
            $this->line("Dispatching health job for {$router->name}...");
            RouterHealthJob::dispatch($router);
        }

        $this->info('All health jobs dispatched.');
        
        return self::SUCCESS;
    }
}
