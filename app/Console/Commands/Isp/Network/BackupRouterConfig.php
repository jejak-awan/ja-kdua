<?php

namespace App\Console\Commands\Isp\Network;

use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\RouterBackupService;
use Illuminate\Console\Command;

class BackupRouterConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'isp:router-backup {router_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger automated backup and export on Mikrotik routers';

    /**
     * Execute the console command.
     */
    public function handle(RouterBackupService $backupService): int
    {
        $routerId = $this->argument('router_id');

        if ($routerId) {
            $routers = ServiceNode::where('id', $routerId)->get();
        } else {
            $routers = ServiceNode::where('type', 'Router')
                ->where('status', 'active')
                ->get();
        }

        if ($routers->isEmpty()) {
            $this->error('No active routers found or specific router ID not found.');
            return 1;
        }

        $this->info("Starting backup process for " . $routers->count() . " router(s)...");

        foreach ($routers as $router) {
            $this->line("Processing: <info>{$router->name}</info> ({$router->ip_address})");
            
            $result = $backupService->triggerBackup($router);
            
            if ($result['success']) {
                $this->info("  [OK] " . $result['message']);
            } else {
                $this->error("  [ERROR] " . $result['message']);
            }
        }

        $this->info('Backup process completed.');

        return 0;
    }
}
