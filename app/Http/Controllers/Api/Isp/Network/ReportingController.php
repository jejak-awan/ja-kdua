<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Network\NodeHealthLog;
use App\Models\Isp\Network\ServiceNode;
use App\Models\Isp\Network\TrafficMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends BaseApiController
{
    /**
     * Get aggregated traffic report for a specific router/interface.
     */
    public function trafficReport(Request $request): JsonResponse
    {
        $request->validate([
            'router_id' => 'required|integer',
            'interface' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'interval' => 'nullable|string|in:1 minute,5 minutes,30 minutes,1 hour,1 day',
        ]);

        $routerId = $request->integer('router_id');
        $interface = $request->string('interface');
        $start = $request->date('start_date');
        $end = $request->date('end_date');
        $interval = $request->string('interval', '1 hour');

        // TimescaleDB time_bucket aggregation
        $query = TrafficMetric::where('router_id', $routerId)
            ->where('interface', $interface)
            ->whereBetween('time', [$start, $end])
            ->select(
                DB::raw("time_bucket('{$interval}', time) AS bucket"),
                DB::raw("AVG(rx_bps) as avg_rx"),
                DB::raw("MAX(rx_bps) as max_rx"),
                DB::raw("AVG(tx_bps) as avg_tx"),
                DB::raw("MAX(tx_bps) as max_tx")
            )
            ->groupBy('bucket')
            ->orderBy('bucket', 'asc');

        return $this->success($query->get(), 'Traffic report generated');
    }

    /**
     * Get SLA availability report for nodes.
     */
    public function slaReport(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'node_id' => 'nullable|integer',
        ]);

        $start = $request->date('start_date');
        $end = $request->date('end_date');
        $nodeId = $request->integer('node_id');

        $query = NodeHealthLog::whereBetween('time', [$start, $end]);

        if ($nodeId) {
            $query->where('node_id', $nodeId);
        }

        $stats = $query->select(
            'node_id',
            DB::raw("COUNT(*) as total_samples"),
            DB::raw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as online_samples"),
            DB::raw("AVG(latency_ms) as avg_latency")
        )
            ->groupBy('node_id')
            ->get()
            ->map(function ($row) {
                /** @var \App\Models\Isp\Network\NodeHealthLog $row */
                $row->availability_pct = (float)($row->total_samples > 0 
                    ? round(($row->online_samples / $row->total_samples) * 100, 2) 
                    : 0);
                
                $node = ServiceNode::find($row->node_id);
                $row->node_name = (string)($node ? $node->name : "Unknown (#{$row->node_id})");
                
                return $row;
            });

        return $this->success($stats, 'SLA report generated');
    }
}
