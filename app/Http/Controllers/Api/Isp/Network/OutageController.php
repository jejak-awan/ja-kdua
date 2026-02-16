<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Network\Outage;
use App\Services\Isp\Network\RouterService;
use App\Services\Isp\ThirdParty\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutageController extends BaseApiController
{
    /**
     * @var RouterService
     */
    protected $routerService;

    /**
     * @var WhatsAppNotificationService
     */
    protected $whatsApp;

    public function __construct(RouterService $routerService, WhatsAppNotificationService $whatsApp)
    {
        $this->routerService = $routerService;
        $this->whatsApp = $whatsApp;
    }

    /**
     * List current and past outages (Admin)
     */
    // ... (index method)
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

        // Broadcast notification if node_id is present
        if ($outage->node_id) {
            $this->broadcastOutage($outage);
        }

        return $this->success($outage, 'Outage report created successfully');
    }

    /**
     * Broadcast outage notification to affected customers.
     */
    protected function broadcastOutage(Outage $outage): void
    {
        /** @var \App\Models\Isp\Network\ServiceNode $node */
        $node = $outage->node;

        // Find customers connected to this node
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Isp\Customer\Customer> $customers */
        $customers = \App\Models\Isp\Customer\Customer::where('router_id', $outage->node_id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        foreach ($customers as $customer) {
            if ($customer->user->phone) {
                $this->whatsApp->sendOutageAlert(
                    $customer->user->phone,
                    $node->name ?? 'Your Area',
                    $outage->status,
                    $outage->description ?? ''
                );
            }
        }
    }

    /**
     * Update an outage report
     */
    public function update(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $outageId = is_numeric($id) ? (int) $id : 0;
        $outage = Outage::find($outageId);

        if (! $outage) {
            return $this->error('Outage report not found', 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:Investigating,Identified,Monitoring,Resolved',
            'description' => 'nullable|string',
            'resolved_at' => 'nullable|date|after_or_equal:started_at',
        ]);

        $outage->update($validated);

        return $this->success($outage, 'Outage status updated successfully');
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
