<?php

namespace App\Http\Controllers\Api\Core;

use App\Models\Core\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginHistoryController extends BaseApiController
{
    /**
     * Get all login history for admin
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $query = LoginHistory::with('user');

            // Filters
            if ($request->has('user_id') && $request->input('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->has('status') && $request->input('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('ip_address') && $request->input('ip_address')) {
                $query->where('ip_address', $request->input('ip_address'));
            }

            if ($request->has('date_from') && $request->input('date_from')) {
                $dateFromRaw = $request->input('date_from');
                $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
                if ($dateFrom) {
                    $query->whereDate('login_at', '>=', $dateFrom);
                }
            }

            if ($request->has('date_to') && $request->input('date_to')) {
                $dateToRaw = $request->input('date_to');
                $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
                if ($dateTo) {
                    $query->whereDate('login_at', '<=', $dateTo);
                }
            }

            // Pagination
            $perPageRaw = $request->input('per_page', 50);
            $perPageInt = is_numeric($perPageRaw) ? (int) $perPageRaw : 50;
            $perPage = min(max($perPageInt, 10), 500);
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
    /**
     * Get login history statistics
     */
    public function statistics(Request $request): \Illuminate\Http\JsonResponse
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
    public function export(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $query = LoginHistory::with('user');

            // Apply same filters as index
            if ($request->has('user_id') && $request->input('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($request->has('status') && $request->input('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('date_from') && $request->input('date_from')) {
                $dateFromRaw = $request->input('date_from');
                $dateFrom = is_string($dateFromRaw) ? $dateFromRaw : null;
                if ($dateFrom) {
                    $query->whereDate('login_at', '>=', $dateFrom);
                }
            }

            if ($request->has('date_to') && $request->input('date_to')) {
                $dateToRaw = $request->input('date_to');
                $dateTo = is_string($dateToRaw) ? $dateToRaw : null;
                if ($dateTo) {
                    $query->whereDate('login_at', '<=', $dateTo);
                }
            }

            $logs = $query->latest('login_at')->limit(10000)->get();

            // Generate CSV
            $csv = "ID,User,Email,Status,IP Address,Login At,Logout At,Duration,Failure Reason\n";
            foreach ($logs as $log) {
                $duration = '';
                if ($log->login_at && $log->logout_at) {
                    $minutes = $log->login_at->diffInMinutes($log->logout_at);
                    $duration = $minutes.' min';
                }

                $csv .= sprintf(
                    "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                    $log->id,
                    $log->user->name ?? 'Unknown',
                    $log->user->email ?? '',
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
                'Content-Disposition' => 'attachment; filename="login-history-'.now()->format('Y-m-d').'.csv"',
            ]);
        } catch (\Exception $e) {
            Log::error('Login history export error: '.$e->getMessage());

            return $this->error('Failed to export login history', 500);
        }
    }

    public function clear(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $retainDaysRaw = $request->input('retain_days');
            $retainDays = is_numeric($retainDaysRaw) ? (int) $retainDaysRaw : null;

            if ($retainDays) {
                $count = LoginHistory::where('login_at', '<', now()->subDays($retainDays))->delete();
                $countInt = is_numeric($count) ? (int) $count : 0;

                return $this->success(null, "Cleared {$countInt} login history records older than {$retainDays} days");
            }

            LoginHistory::truncate();

            return $this->success(null, 'All login history cleared successfully');
        } catch (\Exception $e) {
            Log::error('Login history clear error: '.$e->getMessage());

            return $this->error('Failed to clear login history', 500);
        }
    }
}
