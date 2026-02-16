<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Customer;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Customer\Customer;
use App\Services\Isp\Customer\CustomerProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SelfRegistrationController extends BaseApiController
{
    protected CustomerProvisioningService $provisioning;

    public function __construct(CustomerProvisioningService $provisioning)
    {
        $this->provisioning = $provisioning;
    }

    /**
     * Register customer equipment (ONU) self-service.
     */
    public function registerEquipment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onu_serial' => 'required|string|min:8',
            'onu_model' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', 422, $validator->errors()->toArray());
        }

        /** @var \App\Models\Core\User $user */
        $user = auth()->user();
        $customer = $user->customer ?? null;

        if (! $customer) {
            return $this->error('Customer record not found', 404);
        }

        // 1. Update customer metadata with equipment info
        $customer->update([
            'metadata' => array_merge($customer->metadata ?? [], [
                'onu_serial' => $request->onu_serial,
                'onu_model' => $request->onu_model,
                'registered_at' => now()->toDateTimeString(),
            ]),
        ]);

        // 2. Create Service Request for Provisioning
        /** @var \App\Models\Isp\Support\ServiceRequest $serviceRequest */
        $serviceRequest = \App\Models\Isp\Support\ServiceRequest::create([
            'customer_id' => $customer->id,
            'type' => 'provisioning',
            'status' => 'Pending',
            'details' => [
                'subject' => 'Self-Registration Provisioning',
                'priority' => 'High',
                'onu_serial' => $request->onu_serial,
                'onu_model' => $request->onu_model,
                'source' => 'self-registration',
            ],
        ]);

        // 3. Trigger automated provisioning sequence
        // This will register the ONU on the OLT and sync to RADIUS
        \App\Jobs\Isp\ProcessProvisioningJob::dispatch($serviceRequest);

        return $this->success(null, 'Equipment registered successfully. Provisioning request #'.$serviceRequest->id.' has been created.');
    }

    /**
     * Run a self-diagnostic test for the customer.
     */
    public function diagnose(): JsonResponse
    {
        /** @var \App\Models\Core\User $user */
        $user = auth()->user();
        $customer = $user->customer ?? null;

        if (! $customer) {
            return $this->error('Customer record not found', 404);
        }

        // This triggers a real-time diagnostic check across router/OLT
        /** @var \App\Services\Isp\Support\QuickDiagnosisService $diagnostics */
        $diagnostics = app(\App\Services\Isp\Support\QuickDiagnosisService::class);
        $result = $diagnostics->diagnoseCustomer($customer);

        return $this->success($result, 'Self-diagnostic test completed');
    }
}
