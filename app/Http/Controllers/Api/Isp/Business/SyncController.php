<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Business;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\Customer;
use App\Services\Isp\RouterService;
use Illuminate\Support\Facades\Log;

class SyncController extends BaseApiController
{
    protected RouterService $routerService;

    public function __construct(RouterService $routerService)
    {
        $this->routerService = $routerService;
    }

    /**
     * Sync a specific customer to the router.
     */
    public function syncCustomer(int $id): \Illuminate\Http\JsonResponse
    {
        $customer = Customer::with('plan')->findOrFail($id);

        if ($customer->status !== 'active') {
            return $this->error('Cannot sync inactive customer', 400);
        }

        $success = $this->routerService->syncCustomer($customer);

        if ($success) {
            return $this->success(null, 'Customer synced successfully to router.');
        }

        return $this->error('Failed to sync customer to router. Check logs.', 500);
    }

    /**
     * Sync all active customers to their respective routers.
     */
    public function syncAll(): \Illuminate\Http\JsonResponse
    {
        $customers = Customer::where('status', 'active')
            ->whereNotNull('mikrotik_login')
            ->whereNotNull('router_id')
            ->with('plan')
            ->get();

        $successCount = 0;
        $failCount = 0;

        foreach ($customers as $customer) {
            if ($this->routerService->syncCustomer($customer)) {
                $successCount++;
            } else {
                $failCount++;
                Log::warning("SyncAll: Failed to sync customer ID {$customer->id}");
            }
        }

        return $this->success([
            'total' => $customers->count(),
            'success' => $successCount,
            'failed' => $failCount,
        ], "Sync complete. Success: {$successCount}, Failed: {$failCount}");
    }
}
