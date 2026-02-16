<?php

declare(strict_types=1);

namespace App\Jobs\Isp;

use App\Models\Isp\Network\ServiceNode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrafficAccountingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $routers = ServiceNode::where('type', 'Router')->get();

        foreach ($routers as $router) {
            $this->collectTraffic($router);
        }
    }

    protected function collectTraffic(ServiceNode $router): void
    {
        $ip = $router->ip_address;
        if (!$ip) return;

        $community = 'public'; // or from settings/metadata
        $port = $router->snmp_port ?? 161;

        try {
            // Using snmp2_real_walk if extension exists, otherwise skip
            if (!function_exists('snmp2_real_walk')) {
                Log::warning('SNMP extension not found. Traffic accounting skipped.');
                return;
            }

            // OIDs for High Capacity Counters (64-bit)
            // ifHCInOctets = .1.3.6.1.2.1.31.1.1.1.6
            // ifHCOutOctets = .1.3.6.1.2.1.31.1.1.1.10
            // ifName = .1.3.6.1.2.1.31.1.1.1.1

            $ifNames = @snmp2_real_walk($ip, $community, '.1.3.6.1.2.1.31.1.1.1.1');
            $inOctets = @snmp2_real_walk($ip, $community, '.1.3.6.1.2.1.31.1.1.1.6');
            $outOctets = @snmp2_real_walk($ip, $community, '.1.3.6.1.2.1.31.1.1.1.10');

            if (!$ifNames || !$inOctets || !$outOctets) return;

            foreach ($ifNames as $oid => $val) {
                $index = (string) substr($oid, strrpos($oid, '.') + 1);
                $name = (string) str_replace('STRING: ', '', (string) $val);
                $rx = (int) str_replace('Counter64: ', '', (string) ($inOctets[".1.3.6.1.2.1.31.1.1.1.6.$index"] ?? 0));
                $tx = (int) str_replace('Counter64: ', '', (string) ($outOctets[".1.3.6.1.2.1.31.1.1.1.10.$index"] ?? 0));

                if ($rx > 0 || $tx > 0) {
                    DB::table('isp_traffic_logs')->insert([
                        'service_node_id' => $router->id,
                        'interface_name' => $name,
                        'interface_index' => $index,
                        'rx_octets' => $rx,
                        'tx_octets' => $tx,
                        'logged_at' => now(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error("Traffic Accounting Failed for Router {$router->name}: " . $e->getMessage());
        }
    }
}
