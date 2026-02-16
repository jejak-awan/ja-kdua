<?php

declare(strict_types=1);

namespace App\Services\Isp\Customer;

use App\Jobs\Isp\ProcessProvisioningJob;
use App\Models\Isp\Support\ServiceRequest;
use Illuminate\Support\Facades\Log;

class CustomerProvisioningService
{
    protected \App\Services\Isp\Network\OltService $oltService;

    protected \App\Services\Isp\Network\RadiusService $radius;

    protected \App\Services\Isp\Core\ISPValidationService $validator;

    public function __construct(
        \App\Services\Isp\Network\OltService $oltService,
        \App\Services\Isp\Network\RadiusService $radius,
        \App\Services\Isp\Core\ISPValidationService $validator
    ) {
        $this->oltService = $oltService;
        $this->radius = $radius;
        $this->validator = $validator;
    }

    /**
     * Dispatch the provisioning process as a background job.
     */
    public function executeRequest(ServiceRequest $serviceRequest): bool
    {
        // Add service-level validation boundary
        if ($serviceRequest->customer) {
            $this->validator->validateProvisioning($serviceRequest->customer);
        }

        Log::info("Dispatching asynchronous provisioning for Request ID: {$serviceRequest->id}");

        $serviceRequest->update(['status' => 'Processing']);

        ProcessProvisioningJob::dispatch($serviceRequest);

        return true;
    }
}
