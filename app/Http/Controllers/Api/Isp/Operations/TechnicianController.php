<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Operations;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Core\User;
use App\Models\Isp\Operations\TechnicianDeployment;
use App\Models\Isp\Support\ServiceRequest;
use App\Services\Isp\Operations\DispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TechnicianController extends BaseApiController
{
    public function __construct(
        private readonly DispatchService $dispatchService
    ) {}
    /**
     * List all technicians.
     */
    public function technicians(): JsonResponse
    {
        $technicians = User::whereHas('roles', function ($q) {
            $q->where('name', 'technician');
        })->get();

        return $this->success($technicians, 'Technicians retrieved successfully');
    }

    /**
     * Get recommended technicians for a service request.
     */
    public function recommendations(ServiceRequest $request): JsonResponse
    {
        $technicians = $this->dispatchService->getRecommendedTechnicians($request);

        return $this->success($technicians, 'Recommended technicians retrieved successfully');
    }

    /**
     * List all deployments.
     */
    public function deployments(): JsonResponse
    {
        $deployments = TechnicianDeployment::with(['technician', 'customer', 'serviceRequest'])
            ->latest()
            ->paginate(20);

        return $this->success($deployments, 'Deployments retrieved successfully');
    }

    /**
     * Create a new deployment (Deploy technician).
     */
    public function deploy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'customer_id' => 'required|exists:isp_customers,id',
            'service_request_id' => 'nullable|exists:isp_service_requests,id',
            'type' => 'required|in:installation,repair,maintenance',
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $deployment = TechnicianDeployment::create(array_merge($validated, [
            'status' => 'scheduled',
        ]));

        return $this->success($deployment, 'Technician deployed successfully', 201);
    }

    /**
     * Update deployment status.
     */
    public function updateStatus(Request $request, TechnicianDeployment $deployment): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $updateData = ['status' => $validated['status']];
        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now();
        }
        
        if (isset($validated['notes'])) {
            $updateData['notes'] = $deployment->notes . "\nUpdate: " . $validated['notes'];
        }

        $deployment->update($updateData);

        return $this->success($deployment, 'Deployment status updated successfully');
    }
}
