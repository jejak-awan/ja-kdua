<?php

declare(strict_types=1);

namespace App\Jobs\Isp;

use App\Models\Isp\Network\ServiceNode;
use App\Models\Isp\Network\TrafficMetric;
use App\Services\Isp\Network\RouterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RouterTrafficJob implements ShouldQueue
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
        try {
            $interfaces = $service->getInterfaces($this->router);
            $timestamp = now();

            foreach ($interfaces as $iface) {
                // Only poll running, non-disabled interfaces that aren't virtual bridges/tunnels (optional filter)
                if ($iface['running'] && !$iface['disabled']) {
                    $traffic = $service->getInterfaceTraffic($this->router, $iface['name']);
                    
                    if ($traffic) {
                        TrafficMetric::create([
                            'time' => $timestamp,
                            'router_id' => $this->router->id,
                            'interface' => $iface['name'],
                            'rx_bps' => $traffic['rx'],
                            'tx_bps' => $traffic['tx'],
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("RouterTrafficJob Error for {$this->router->name}: " . $e->getMessage());
        }
    }
}
