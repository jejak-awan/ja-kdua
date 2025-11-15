<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(50);

        return $this->paginated($logs, 'Activity logs retrieved successfully');
    }

    public function show(ActivityLog $activityLog)
    {
        return $this->success($activityLog->load('user'), 'Activity log retrieved successfully');
    }

    public function userActivity(Request $request, $userId)
    {
        $logs = ActivityLog::where('user_id', $userId)
            ->with('user')
            ->latest()
            ->paginate(50);

        return $this->paginated($logs, 'User activity logs retrieved successfully');
    }

    public function recent(Request $request)
    {
        $limit = $request->input('limit', 20);
        
        $logs = ActivityLog::with('user')
            ->latest()
            ->limit($limit)
            ->get();

        return $this->success($logs, 'Recent activity logs retrieved successfully');
    }

    public function statistics(Request $request)
    {
        $query = ActivityLog::query();

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $stats = [
            'total_actions' => $query->count(),
            'actions_by_type' => $query->selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'actions_by_user' => $query->selectRaw('user_id, count(*) as count')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->with('user:id,name,email')
                ->get()
                ->map(function ($item) {
                    return [
                        'user' => $item->user,
                        'count' => $item->count,
                    ];
                }),
            'actions_by_model' => $query->selectRaw('model_type, count(*) as count')
                ->whereNotNull('model_type')
                ->groupBy('model_type')
                ->pluck('count', 'model_type'),
        ];

        return $this->success($stats, 'Activity log statistics retrieved successfully');
    }
}
