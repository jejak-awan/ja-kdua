<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Isp\ServiceRequest;
use App\Services\Isp\ProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceRequestController extends BaseApiController
{
    protected ProvisioningService $provisioning;

    public function __construct(ProvisioningService $provisioning)
    {
        $this->provisioning = $provisioning;
    }

    /**
     * Display a listing of service requests.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = ServiceRequest::with('customer')->latest();

        if ($request->has('status')) {
            $status = $request->input('status');
            if (is_string($status) && $status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($request->has('type')) {
            $type = $request->input('type');
            if (is_string($type)) {
                $query->where('type', $type);
            }
        }

        $perPage = $request->input('per_page');
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 15;
        $requests = $query->paginate($perPageInt);

        return $this->success($requests, 'Service requests retrieved successfully');
    }

    /**
     * Update the status of a service request.
     */
    public function updateStatus(Request $request, ServiceRequest $serviceRequest): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Approved', 'Rejected', 'Completed'])],
            'admin_notes' => 'nullable|string',
        ]);

        $serviceRequest->update($validated);

        return $this->success($serviceRequest, 'Service request status updated successfully');
    }

    /**
     * Execute the service request (Finalize provisioning).
     */
    public function execute(ServiceRequest $serviceRequest): \Illuminate\Http\JsonResponse
    {
        if ($serviceRequest->status !== 'Approved') {
            return $this->error('Only approved requests can be executed', 400);
        }

        try {
            DB::beginTransaction();

            $result = $this->provisioning->executeRequest($serviceRequest);

            if (! $result) {
                throw new \Exception('Failed to execute provisioning logic');
            }

            $serviceRequest->update(['status' => 'Completed']);

            DB::commit();

            return $this->success($serviceRequest, 'Service request executed successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), 500);
        }
    }
}
