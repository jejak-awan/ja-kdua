<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginHistoryController extends BaseApiController
{
    /**
     * Get all login history for admin
     */
    public function index(Request $request)
    {
        try {
            $query = LoginHistory::with('user');

            // Filters
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('ip_address') && $request->ip_address) {
                $query->where('ip_address', $request->ip_address);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('login_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('login_at', '<=', $request->date_to);
            }

            // Pagination
            $perPage = min(max((int) $request->input('per_page', 50), 10), 500);
            $logs = $query->latest('login_at')->paginate($perPage);

            return $this->paginated($logs, 'Login history retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Login history index error: '.$e->getMessage());
            return $this->success([], 'Login history retrieved successfully');
        }
    }

    /**
     * Get login history statistics
     */
    public function statistics(Request $request)
    {
        try {
            $stats = [
                'total_logins' => LoginHistory::where('status', 'success')->count(),
                'failed_logins' => LoginHistory::where('status', 'failed')->count(),
                'today_logins' => LoginHistory::where('status', 'success')
                    ->whereDate('login_at', today())->count(),
                'unique_ips_today' => LoginHistory::whereDate('login_at', today())
                    ->distinct('ip_address')->count('ip_address'),
                'active_sessions' => LoginHistory::whereNull('logout_at')
                    ->where('status', 'success')->count(),
            ];

            return $this->success($stats, 'Login history statistics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Login history statistics error: '.$e->getMessage());
            return $this->success([
                'total_logins' => 0,
                'failed_logins' => 0,
                'today_logins' => 0,
                'unique_ips_today' => 0,
                'active_sessions' => 0,
            ], 'Login history statistics retrieved successfully');
        }
    }

    /**
     * Export login history to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = LoginHistory::with('user');

            // Apply same filters as index
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('login_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('login_at', '<=', $request->date_to);
            }

            $logs = $query->latest('login_at')->limit(10000)->get();

            // Generate CSV
            $csv = "ID,User,Email,Status,IP Address,Login At,Logout At,Duration,Failure Reason\n";
            foreach ($logs as $log) {
                $duration = '';
                if ($log->login_at && $log->logout_at) {
                    $minutes = $log->login_at->diffInMinutes($log->logout_at);
                    $duration = $minutes . ' min';
                }
                
                $csv .= sprintf(
                    "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                    $log->id,
                    $log->user?->name ?? 'Unknown',
                    $log->user?->email ?? '',
                    $log->status,
                    $log->ip_address ?? '',
                    $log->login_at?->format('Y-m-d H:i:s') ?? '',
                    $log->logout_at?->format('Y-m-d H:i:s') ?? '',
                    $duration,
                    str_replace('"', '""', $log->failure_reason ?? '')
                );
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="login-history-' . now()->format('Y-m-d') . '.csv"',
            ]);
        } catch (\Exception $e) {
            Log::error('Login history export error: '.$e->getMessage());
            return $this->error('Failed to export login history', 500);
        }
            return $this->error('Failed to export login history', 500);
        }
    }

    public function clear(Request $request)
    {
        try {
            $retainDays = $request->input('retain_days');

            if ($retainDays) {
                $count = LoginHistory::where('login_at', '<', now()->subDays($retainDays))->delete();
                return $this->success(null, "Cleared $count login history records older than $retainDays days");
            }

            LoginHistory::truncate();
            return $this->success(null, 'All login history cleared successfully');
        } catch (\Exception $e) {
            Log::error('Login history clear error: '.$e->getMessage());
            return $this->error('Failed to clear login history', 500);
        }
    }
}
