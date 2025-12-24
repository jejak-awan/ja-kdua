<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends BaseApiController
{
    public function index(Request $request)
    {
        try {
            $query = ActivityLog::with('user');

            // Filters
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('action') && $request->action) {
                $query->where('action', $request->action);
            }

            if ($request->has('model_type') && $request->model_type) {
                $query->where('model_type', 'like', '%' . $request->model_type . '%');
            }

            if ($request->has('ip_address') && $request->ip_address) {
                $query->where('ip_address', $request->ip_address);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('model_type', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDir = $request->input('sort_dir', 'desc');
            $allowedSorts = ['created_at', 'action', 'model_type', 'user_id'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
            } else {
                $query->latest();
            }

            // Pagination with customizable per_page
            $perPage = min(max((int) $request->input('per_page', 50), 10), 500);
            $logs = $query->paginate($perPage);

            return $this->paginated($logs, 'Activity logs retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Activity logs index error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->success([], 'Activity logs retrieved successfully');
        }
    }

    /**
     * Export activity logs to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = ActivityLog::with('user');

            // Apply same filters as index
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('action') && $request->action) {
                $query->where('action', $request->action);
            }

            if ($request->has('model_type') && $request->model_type) {
                $query->where('model_type', 'like', '%' . $request->model_type . '%');
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $logs = $query->latest()->limit(10000)->get();

            // Generate CSV content
            $csv = "ID,User,Action,Model Type,Model ID,Description,IP Address,Created At\n";
            foreach ($logs as $log) {
                $csv .= sprintf(
                    "%d,\"%s\",\"%s\",\"%s\",%s,\"%s\",\"%s\",\"%s\"\n",
                    $log->id,
                    $log->user?->name ?? 'System',
                    $log->action,
                    $log->model_type ?? '',
                    $log->model_id ?? '',
                    str_replace('"', '""', $log->description ?? ''),
                    $log->ip_address ?? '',
                    $log->created_at->format('Y-m-d H:i:s')
                );
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="activity-logs-' . now()->format('Y-m-d') . '.csv"',
            ]);
        } catch (\Exception $e) {
            Log::error('Activity logs export error: '.$e->getMessage());
            return $this->error('Failed to export activity logs', 500);
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

    public function clear(Request $request)
    {
        try {
            // Optional: retain last X days
            $retainDays = $request->input('retain_days');

            if ($retainDays) {
                $count = ActivityLog::where('created_at', '<', now()->subDays($retainDays))->delete();
                return $this->success(null, "Cleared $count activity logs older than $retainDays days");
            }

            ActivityLog::truncate();
            return $this->success(null, 'All activity logs cleared successfully');
        } catch (\Exception $e) {
            Log::error('Activity logs clear error: '.$e->getMessage());
            return $this->error('Failed to clear activity logs', 500);
        }
    }
}
