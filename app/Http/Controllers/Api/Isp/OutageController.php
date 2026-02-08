<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\Outage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OutageController extends BaseApiController
{
    /**
     * List current and past outages (Admin)
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $outages = Outage::with('node')->latest()->get();

        return $this->success($outages);
    }

    /**
     * Create a new outage report
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'node_id' => 'nullable|exists:isp_service_nodes,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|in:Scheduled,Unscheduled',
            'status' => 'required|in:Investigating,Identified,Monitoring,Resolved',
            'started_at' => 'required|date',
            'resolved_at' => 'nullable|date|after_or_equal:started_at',
        ]);

        $outage = Outage::create($validated);

        return $this->success($outage, 'Outage report created successfully');
    }

    /**
     * Update an outage report
     */
    public function update(Request $request, mixed $id): \Illuminate\Http\JsonResponse
    {
        $id = is_numeric($id) ? (int) $id : 0;
        $outage = Outage::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:Investigating,Identified,Monitoring,Resolved',
            'description' => 'nullable|string',
            'resolved_at' => 'nullable|date|after_or_equal:started_at',
        ]);

        $outage->update($validated);

        return $this->success($outage, 'Outage status updated');
    }

    /**
     * Public Status (No Auth)
     */
    public function publicStatus(): \Illuminate\Http\JsonResponse
    {
        $activeIncidents = Outage::where('status', '!=', 'Resolved')
            ->with(['node' => function ($query) {
                $query->select('id', 'name', 'type');
            }])
            ->latest()
            ->get();

        $recentResolved = Outage::where('status', 'Resolved')
            ->where('resolved_at', '>=', Carbon::now()->subDays(7))
            ->with(['node' => function ($query) {
                $query->select('id', 'name', 'type');
            }])
            ->latest()
            ->limit(5)
            ->get();

        // Calculate general health
        $isHealthy = $activeIncidents->isEmpty();

        return $this->success([
            'is_healthy' => $isHealthy,
            'active_incidents' => $activeIncidents,
            'recent_resolved' => $recentResolved,
            'last_updated' => Carbon::now()->toIso8601String(),
        ]);
    }
}
