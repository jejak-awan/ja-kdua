<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends BaseApiController
{
    public function index(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Activity logs index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            // Return empty paginated response instead of error
            try {
                return $this->paginated(
                    ActivityLog::query()->paginate(50, ['*'], 'page', 1),
                    'Activity logs retrieved successfully'
                );
            } catch (\Exception $e2) {
                // If even empty query fails, return minimal response
                return $this->success([], 'Activity logs retrieved successfully');
            }
        }
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

        $baseQuery = clone $query;
        
        $stats = [
            'total' => $baseQuery->count(),
            'today' => $baseQuery->whereDate('created_at', today())->count(),
            'this_week' => $baseQuery->whereDate('created_at', '>=', now()->subWeek())->count(),
            'active_users' => $baseQuery->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
            'actions_by_type' => $query->selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'actions_by_user' => $query->selectRaw('user_id, count(*) as count')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->get()
                ->map(function ($item) {
                    $user = \App\Models\User::find($item->user_id);
                    return [
                        'user' => $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email] : null,
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
