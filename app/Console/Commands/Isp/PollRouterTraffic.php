<?php

declare(strict_types=1);

namespace App\Console\Commands\Isp;

use App\Jobs\Isp\RouterTrafficJob;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Console\Command;

class PollRouterTraffic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:poll-traffic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch traffic polling jobs for all active routers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting ISP Traffic Poller...');
        
        $routers = ServiceNode::where('type', 'Router')->where('status', 'active')->get();
        
        $this->info("Found {$routers->count()} active routers to poll.");

        foreach ($routers as $router) {
            $this->line("Dispatching traffic monitoring job for {$router->name}...");
            RouterTrafficJob::dispatch($router);
        }

        $this->info('All traffic monitoring jobs dispatched.');
        
        return self::SUCCESS;
    }
}

