<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\SecurityService;
use App\Services\CacheWarmingService;
use App\Services\QueryPerformanceService;
use App\Services\SystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class SystemController extends BaseApiController
{
    protected SystemService $systemService;

    public function __construct()
    {
        $this->systemService = new SystemService();
    }

    public function info()
    {
        return $this->success($this->systemService->getSystemInfo(), 'System information retrieved successfully');
    }

    public function health()
    {
        $securityService = new SecurityService();
        $health = $securityService->checkSystemHealth();

        $health['php'] = ['status' => 'ok', 'message' => 'PHP ' . PHP_VERSION];
        $health['laravel'] = ['status' => 'ok', 'message' => 'Laravel ' . app()->version()];

        try {
            Cache::put('health_check_queue', 'test', 10);
            $health['queue'] = ['status' => 'ok', 'message' => 'Queue connection working'];
        } catch (\Exception $e) {
            $health['queue'] = ['status' => 'error', 'message' => 'Queue connection failed'];
        }

        return $this->success($health, 'System health check completed');
    }

    public function statistics()
    {
        return $this->success($this->systemService->getStatistics(), 'System statistics retrieved successfully');
    }

    public function cache()
    {
        return $this->success([
            'driver' => config('cache.default'),
            'size' => $this->systemService->getCacheSize(),
        ], 'Cache information retrieved successfully');
    }

    public function cacheStatus()
    {
        $status = $this->systemService->getCacheStatus();
        return $this->success($status, 'Cache status retrieved successfully');
    }

    public function clearCache()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');

        return $this->success(null, 'All caches cleared successfully');
    }

    /**
     * Warm up application cache
     */
    public function warmCache(Request $request)
    {
        try {
            $warmingService = new CacheWarmingService;
            $type = $request->input('type');
            $limit = (int) $request->input('limit', 50);

            if ($type) {
                $count = $warmingService->warmByType($type, $limit);
                return $this->success([
                    'type' => $type,
                    'items_cached' => $count,
                ], "Cache warmed for {$type}: {$count} items");
            } else {
                $results = $warmingService->warmAll();
                $total = array_sum($results);

                return $this->success([
                    'results' => $results,
                    'total_items' => $total,
                ], "Cache warming completed: {$total} total items");
            }
        } catch (\Exception $e) {
            Log::error('Cache warming failed: ' . $e->getMessage());

            return $this->error('Failed to warm cache: ' . $e->getMessage(), 500, [], 'CACHE_WARMING_FAILED');
        }
    }

    /**
     * Get cache warming statistics
     */
    public function cacheWarmingStats()
    {
        try {
            $warmingService = new CacheWarmingService;
            $stats = $warmingService->getStatistics();

            return $this->success($stats, 'Cache warming statistics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to get cache warming stats: ' . $e->getMessage());

            return $this->error('Failed to get cache warming statistics', 500, [], 'STATS_ERROR');
        }
    }

    public function systemHealth()
    {
        $health = $this->systemService->getSystemHealth();
        return $this->success($health, 'System health retrieved successfully');
    }

    // Protected methods removed as they are now handled by SystemService
    // formatBytes moved to private helper if widely used, or just kept in Service

    /**
     * Clear rate limit for login attempts
     */
    public function clearRateLimit(Request $request)
    {
        try {
            $ip = $request->input('ip', $request->ip());
            $email = $request->input('email');

            $cleared = [];

            // Clear throttle rate limit for IP (try multiple key formats)
            $throttleKeys = [
                "throttle:5,1:{$ip}",
                "throttle:10,1:{$ip}",
                "throttle:60,1:{$ip}",
                "throttle:120,1:{$ip}",
            ];
            
            foreach ($throttleKeys as $throttleKey) {
                RateLimiter::clear($throttleKey);
                Cache::forget($throttleKey);
            }
            $cleared[] = "Rate limit for IP: {$ip}";

            // Clear security service related caches
            Cache::forget("failed_login_attempts_{$ip}");
            Cache::forget("blocked_ip_{$ip}");

            // Clear account lockout if email provided
            if ($email) {
                Cache::forget("account_locked_{$email}");
                Cache::forget("failed_login_attempts_email_{$email}");
                $cleared[] = "Account lockout for email: {$email}";
            }

            // If using database cache, also clear from cache table
            if (config('cache.default') === 'database') {
                try {
                    DB::table('cache')->where('key', 'like', "%throttle:5,1:{$ip}%")->delete();
                    DB::table('cache')->where('key', 'like', "%throttle:10,1:{$ip}%")->delete();
                    DB::table('cache')->where('key', 'like', "%throttle:60,1:{$ip}%")->delete();
                    DB::table('cache')->where('key', 'like', "%throttle:120,1:{$ip}%")->delete();
                    DB::table('cache')->where('key', 'like', "%failed_login_attempts_{$ip}%")->delete();
                    DB::table('cache')->where('key', 'like', "%blocked_ip_{$ip}%")->delete();
                    if ($email) {
                        DB::table('cache')->where('key', 'like', "%account_locked_{$email}%")->delete();
                        DB::table('cache')->where('key', 'like', "%failed_login_attempts_email_{$email}%")->delete();
                    }
                    $cleared[] = "Database cache entries cleared";
                } catch (\Exception $e) {
                    Log::warning('Failed to clear database cache: '.$e->getMessage());
                }
            }

            return $this->success([
                'cleared' => $cleared,
                'ip' => $ip,
                'email' => $email,
            ], 'Rate limit cleared successfully');
        } catch (\Exception $e) {
            Log::error('Failed to clear rate limit: '.$e->getMessage());

            return $this->error('Failed to clear rate limit: '.$e->getMessage(), 500);
        }
    }
}
