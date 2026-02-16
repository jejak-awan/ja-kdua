<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Support;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Customer\Customer;
use App\Models\Isp\Network\ServiceNode;
use App\Models\Isp\Support\ServiceRequest;
use App\Models\Isp\Support\Ticket;
use App\Services\Isp\Customer\CustomerProvisioningService;
use App\Services\Isp\Support\QuickDiagnosisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupportController extends BaseApiController
{
    protected QuickDiagnosisService $diagnosisService;

    protected CustomerProvisioningService $provisioning;

    public function __construct(
        QuickDiagnosisService $diagnosisService,
        CustomerProvisioningService $provisioning
    ) {
        $this->diagnosisService = $diagnosisService;
        $this->provisioning = $provisioning;
    }

    // --- Support Tickets ---

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        $query = Ticket::with('user')->latest();

        if ($user->hasRole('ISP Member')) {
            $query->where('user_id', (int) $user->id);
        }

        if ($request->has('status')) {
            $status = $request->input('status');
            if (is_string($status)) {
                $query->where('status', $status);
            }
        }

        if ($request->has('category')) {
            $category = $request->input('category');
            if (is_string($category)) {
                $query->where('category', $category);
            }
        }

        $perPage = $request->input('per_page');
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 15;

        return $this->success($query->paginate($perPageInt), 'Tickets retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => ['required', Rule::in(['Technical', 'Billing', 'Sales'])],
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High'])],
            'message' => 'required|string',
        ]);

        $ticket = Ticket::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'status' => 'Open',
        ]));

        return $this->success($ticket, 'Ticket created successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->unauthorized();
        }

        $ticket = Ticket::with('user')->findOrFail($id);

        if ($user->hasRole('ISP Member') && (int) $ticket->user_id !== (int) $user->id) {
            return $this->forbidden('You are not authorized to view this ticket');
        }

        return $this->success($ticket, 'Ticket details retrieved successfully');
    }

    public function updateTicketStatus(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user || $user->hasRole('ISP Member')) {
            return $this->forbidden('Unauthorized to update ticket status');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['Open', 'In Progress', 'Resolved', 'Closed'])],
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update($validated);

        return $this->success($ticket, 'Ticket status updated successfully');
    }

    // --- Diagnosis ---

    public function diagnoseRouter(int $id): JsonResponse
    {
        $router = ServiceNode::findOrFail($id);
        $result = $this->diagnosisService->diagnoseRouter($router);

        return $this->success($result, 'Router diagnosis completed');
    }

    public function diagnoseCustomer(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $result = $this->diagnosisService->diagnoseCustomer($customer);

        return $this->success($result, 'Customer diagnosis completed');
    }

    // --- Service Requests ---

    public function requestIndex(Request $request): JsonResponse
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

        $perPage = $request->integer('per_page', 15);

        return $this->success($query->paginate($perPage), 'Service requests retrieved successfully');
    }

    public function updateRequestStatus(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Approved', 'Rejected', 'Completed'])],
            'admin_notes' => 'nullable|string',
        ]);

        $serviceRequest->update($validated);

        return $this->success($serviceRequest, 'Service request status updated successfully');
    }

    public function executeRequest(ServiceRequest $serviceRequest): JsonResponse
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
