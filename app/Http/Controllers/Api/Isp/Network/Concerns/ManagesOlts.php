<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Network\Concerns;

use App\Models\Isp\Network\OltCommandLog;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @property \App\Services\Isp\Network\OltService $oltService
 * @property \App\Services\Isp\ZtpDiscoveryService $ztpService
 * @mixin \App\Http\Controllers\Api\Isp\Network\InfrastructureController
 */
trait ManagesOlts
{
    public function oltIndex(Request $request): JsonResponse
    {
        $query = ServiceNode::where('type', 'OLT');

        if ($request->boolean('with_stats')) {
            $query->withCount(['customers', 'odps']);
        }

        $olts = $query->get();

        if ($request->boolean('with_discovery')) {
            $olts->each(function ($olt) {
                $olt->discovery_pending = (array) Cache::get("ztp_discovery_results_{$olt->id}", []);
            });
        }

        return $this->success($olts, 'OLTs retrieved successfully');
    }

    public function testOltConnection(ServiceNode $olt): JsonResponse
    {
        $success = $this->oltService->testConnection($olt);
        if ($success) {
            return $this->success(null, "Connection to {$olt->name} successful");
        }

        return $this->error("Failed to connect to {$olt->name}. Check credentials and access.");
    }

    public function oltLogs(Request $request, ?int $oltId = null): JsonResponse
    {
        $query = OltCommandLog::with(['olt', 'user']);
        if ($oltId) {
            $query->where('olt_id', $oltId);
        } elseif ($request->has('olt_id')) {
            $query->where('olt_id', $request->olt_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $perPage = $request->input('per_page', 20);
        $perPageInt = is_numeric($perPage) ? (int) $perPage : 20;

        return $this->success($query->latest()->paginate($perPageInt), 'OLT command logs retrieved successfully');
    }

    public function oltDiscover(Request $request, ?ServiceNode $olt = null): JsonResponse
    {
        if ($olt) {
            try {
                $driver = $this->oltService->getDriver($olt);
                $results = $driver->discoverUnconfiguredOnus();
                Cache::put("ztp_discovery_results_{$olt->id}", $results, 3600);

                return $this->success($results, "Discovery completed for {$olt->name}");
            } catch (\Exception $e) {
                return $this->error('Discovery failed: '.$e->getMessage());
            }
        }

        $this->ztpService->scanAllOlts();

        return $this->success(null, 'Network-wide discovery started');
    }
}
