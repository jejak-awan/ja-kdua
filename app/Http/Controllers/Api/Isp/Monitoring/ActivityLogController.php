<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp\Monitoring;

use App\Http\Controllers\Api\Core\BaseApiController;
use App\Models\Isp\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends BaseApiController
{
    /**
     * List activity logs with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user:id,name')
            ->orderByDesc('created_at');

        // Filter by action
        $action = $request->input('action');
        if (is_string($action) && $action !== '') {
            $query->where('action', $action);
        }

        // Filter by resource type
        $resourceType = $request->input('resource_type');
        if (is_string($resourceType) && $resourceType !== '') {
            $query->where('resource_type', $resourceType);
        }

        // Filter by user
        $userId = $request->input('user_id');
        if (is_numeric($userId)) {
            $query->where('user_id', (int) $userId);
        }

        // Search
        $search = $request->input('search');
        if (is_string($search) && $search !== '') {
            $query->where('description', 'like', '%'.$search.'%');
        }

        // Date filter
        $from = $request->input('from');
        $to = $request->input('to');
        if (is_string($from) && $from !== '') {
            $query->where('created_at', '>=', $from);
        }
        if (is_string($to) && $to !== '') {
            $query->where('created_at', '<=', $to.' 23:59:59');
        }

        $perPage = is_numeric($request->input('per_page')) ? (int) $request->input('per_page') : 25;

        return $this->success([
            'data' => $query->paginate($perPage),
            'colors' => ActivityLog::actionColors(),
        ]);
    }
}
