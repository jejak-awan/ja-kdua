<?php

namespace App\Jobs\Isp;

use App\Models\Isp\Network\ServiceNode;
use App\Services\Isp\Network\MikrotikService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordNodeHealthHistoryJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(MikrotikService $mikrotik): void
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ServiceNode> $nodes */
        $nodes = ServiceNode::where('status', 'active')->get();

        foreach ($nodes as $node) {
            try {
                $stats = $mikrotik->getNodeStats($node);
                
                //Stats structure from MikrotikService: ['node_id', 'name', 'status', 'latency', 'cpu', 'memory_used', 'memory_total', 'traffic_in', 'traffic_out', 'active_sessions']
                
                DB::table('isp_node_health_history')->insert([
                    'service_node_id' => $node->id,
                    'cpu_load' => $stats['cpu_load'],
                    'memory_used' => $stats['memory_used'],
                    'memory_total' => $stats['memory_total'],
                    'traffic_in_bps' => (int)($stats['traffic_in'] * 1024 * 1024), // Convert M to bps approximately
                    'traffic_out_bps' => (int)($stats['traffic_out'] * 1024 * 1024),
                    'active_sessions' => $stats['active_clients'],
                    'latency_ms' => $stats['latency'],
                    'recorded_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error("RecordNodeHealthHistoryJob: Failed to record stats for node #{$node->id}: " . $e->getMessage());
            }
        }
    }
}
