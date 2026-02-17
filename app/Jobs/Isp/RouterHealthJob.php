<?php

declare(strict_types=1);

namespace App\Jobs\Isp;

use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\RouterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RouterHealthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ServiceNode $router;

    /**
     * Create a new job instance.
     */
    public function __construct(ServiceNode $router)
    {
        $this->router = $router;
    }

    /**
     * Execute the job.
     */
    public function handle(RouterService $service): void
    {
        if ($this->router->connection_method === 'none') {
            return;
        }

        try {
            Log::info("Health Poller: Checking router {$this->router->name} ({$this->router->ip_address})");
            
            // Use getMonitoredStats to populate cache for dashboard and get all metrics in one connection
            $stats = $service->getMonitoredStats($this->router);
            
            // Map stats back to expected format for health check logic
            $isConnected = !empty($stats['resource']);
            $resources = $stats['resource'] ?? [];
            
            $status = [
                'is_connected' => $isConnected,
                'active_count' => $stats['active_count'] ?? 0,
            ];
            
            $cacheKey = "router_status_{$this->router->id}";
            $failCountKey = "router_fail_count_{$this->router->id}";
            
            // Cache the status for 5 minutes
            Cache::put($cacheKey, array_merge($status, ['resource' => $resources]), 300);

            if ($status['is_connected']) {
                $this->handleOnline($failCountKey);
            } else {
                $this->handleOffline($failCountKey);
            }

            // Update the router's status in DB
            /** @var array<string, mixed> $existingMetadata */
            $existingMetadata = $this->router->metadata ?? [];
            
            $this->router->update([
                'status' => $status['is_connected'] ? 'active' : 'offline',
                'metadata' => array_merge($existingMetadata, [
                    'last_poll' => now()->toDateTimeString(),
                    'cpu_load' => $resources['cpu'] ?? 0,
                    'uptime' => $resources['uptime'] ?? '0s',
                ]),
            ]);

            Log::info("Health Poller: Router {$this->router->name} is " . ($status['is_connected'] ? 'ONLINE' : 'OFFLINE'));

            // Log to MRTG/SLA Database
            \App\Models\Isp\Network\NodeHealthLog::create([
                'time' => now(),
                'node_id' => $this->router->id,
                'status' => $status['is_connected'] ? 'active' : 'offline',
                'latency_ms' => 0, // Ping latency could be added here if service supports it
                'packet_loss' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error("Health Poller Error for {$this->router->name}: " . $e->getMessage());
        }
    }

    protected function handleOnline(string $failCountKey): void
    {
        $val = Cache::get($failCountKey);
        $failCount = is_numeric($val) ? (int) $val : 0;
        if ($failCount > 0) {
            Cache::forget($failCountKey);
            Log::info("Health Poller: Router {$this->router->name} is back online. Failure counter reset.");
            
            // Resolve active outage if exists
            \App\Models\Isp\Network\Outage::where('node_id', $this->router->id)
                ->where('status', '!=', 'Resolved')
                ->update([
                    'status' => 'Resolved',
                    'resolved_at' => now(),
                ]);
        }
    }

    protected function handleOffline(string $failCountKey): void
    {
        $failCount = (int) Cache::increment($failCountKey);
        Log::warning("Health Poller: Router {$this->router->name} is OFFLINE. Failure count: {$failCount}");

        // Threshold for auto-incident: 3 consecutive failures
        if ($failCount === 3) {
            $this->triggerAutoIncident();
        }
    }

    protected function triggerAutoIncident(): void
    {
        Log::error("Health Poller: 3 consecutive failures for {$this->router->name}. Triggering auto-incident.");

        // 1. Create Outage
        $outage = \App\Models\Isp\Network\Outage::create([
            'node_id' => $this->router->id,
            'title' => "Node Failure: {$this->router->name}",
            'description' => "Automated detection: Node {$this->router->name} has failed 3 consecutive health checks.",
            'type' => 'Unscheduled',
            'status' => 'Investigating',
            'started_at' => now(),
        ]);

        // 2. Create Support Ticket (Assigned to first admin for now)
        $admin = \App\Models\Core\User::role('admin')->first() ?? \App\Models\Core\User::first();
        
        $ticket = \App\Models\Isp\Support\Ticket::create([
            'user_id' => $admin ? $admin->id : 1,
            'subject' => "CRITICAL: Node Down - {$this->router->name}",
            'category' => 'Technical',
            'priority' => 'High',
            'status' => 'Open',
            'message' => "An automated outage has been detected for node {$this->router->name}.\n\nOutage ID: #{$outage->id}\nTime: " . now()->format('Y-m-d H:i:s'),
            'outage_id' => $outage->id,
        ]);

        // 3. Notify Technicians via Telegram
        /** @var \App\Services\Isp\ThirdParty\TelegramNotificationService $telegram */
        $telegram = app(\App\Services\Isp\ThirdParty\TelegramNotificationService::class);
        $telegram->sendAdminAlert("🚨 *CRITICAL OUTAGE DETECTED* 🚨\n\nNode: *{$this->router->name}*\nTicket: #{$ticket->id}\n\nAutomated incident tracking initiated.");
    }
}
