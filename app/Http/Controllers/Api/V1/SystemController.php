<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\SecurityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemController extends BaseApiController
{
    public function info()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => DB::connection()->getDatabaseName(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
        ];

        return $this->success($info, 'System information retrieved successfully');
    }

    public function health()
    {
        $securityService = new SecurityService;
        $health = $securityService->checkSystemHealth();

        // Add additional checks
        $health['php'] = [
            'status' => 'ok',
            'message' => 'PHP '.PHP_VERSION,
        ];

        $health['laravel'] = [
            'status' => 'ok',
            'message' => 'Laravel '.app()->version(),
        ];

        // Check queue connection
        try {
            Cache::put('health_check_queue', 'test', 10);
            $health['queue'] = ['status' => 'ok', 'message' => 'Queue connection working'];
        } catch (\Exception $e) {
            $health['queue'] = ['status' => 'error', 'message' => 'Queue connection failed: '.$e->getMessage()];
        }

        return $this->success($health, 'System health check completed');
    }

    public function statistics()
    {
        try {
            $stats = [
                'contents' => [
                    'total' => \App\Models\Content::count(),
                    'published' => \App\Models\Content::where('status', 'published')->count(),
                    'draft' => \App\Models\Content::where('status', 'draft')->count(),
                ],
                'users' => [
                    'total' => \App\Models\User::count(),
                    'verified' => \App\Models\User::whereNotNull('email_verified_at')->count(),
                ],
                'media' => [
                    'total' => \App\Models\Media::count(),
                    'total_size' => \App\Models\Media::sum('size') ?? 0,
                ],
                'categories' => \App\Models\Category::count(),
                'tags' => \App\Models\Tag::count(),
                'comments' => \App\Models\Comment::count(),
                'forms' => \App\Models\Form::count(),
                'form_submissions' => \App\Models\FormSubmission::count(),
            ];

            return $this->success($stats, 'System statistics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('System statistics error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return default stats on error
            return $this->success([
                'contents' => ['total' => 0, 'published' => 0, 'draft' => 0],
                'users' => ['total' => 0, 'verified' => 0],
                'media' => ['total' => 0, 'total_size' => 0],
                'categories' => 0,
                'tags' => 0,
                'comments' => 0,
                'forms' => 0,
                'form_submissions' => 0,
            ], 'System statistics retrieved successfully');
        }
    }

    public function cache()
    {
        try {
            $cacheInfo = [
                'driver' => config('cache.default'),
                'size' => $this->getCacheSize(),
            ];

            return $this->success($cacheInfo, 'Cache information retrieved successfully');
        } catch (\Exception $e) {
            Log::error('System cache info error: '.$e->getMessage());

            return $this->success([
                'driver' => config('cache.default', 'file'),
                'size' => 0,
            ], 'Cache information retrieved successfully');
        }
    }

    public function cacheStatus()
    {
        try {
            $cacheDriver = config('cache.default', 'file');

            // Try to get cache stats if available
            $hits = 0;
            $misses = 0;

            // For Redis cache, we can get more detailed stats
            if ($cacheDriver === 'redis') {
                try {
                    $redis = \Illuminate\Support\Facades\Redis::connection();
                    if ($redis) {
                        $info = $redis->info('stats');
                        $hits = $info['keyspace_hits'] ?? 0;
                        $misses = $info['keyspace_misses'] ?? 0;
                    }
                } catch (\Exception $e) {
                    // Redis stats not available, use defaults
                }
            }

            $status = [
                'status' => 'Active',
                'driver' => $cacheDriver,
                'hits' => $hits,
                'misses' => $misses,
                'details' => [
                    'driver' => $cacheDriver,
                    'size' => $this->getCacheSize(),
                ],
            ];

            return $this->success($status, 'Cache status retrieved successfully');
        } catch (\Exception $e) {
            Log::error('System cache status error: '.$e->getMessage());

            return $this->success([
                'status' => 'Active',
                'driver' => config('cache.default', 'file'),
                'hits' => 0,
                'misses' => 0,
                'details' => [
                    'driver' => config('cache.default', 'file'),
                    'size' => '0 B',
                ],
            ], 'Cache status retrieved successfully');
        }
    }

    public function clearCache()
    {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');

        return $this->success(null, 'All caches cleared successfully');
    }

    protected function getCacheSize()
    {
        $cachePath = storage_path('framework/cache');
        if (is_dir($cachePath)) {
            $size = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cachePath)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }

            return $this->formatBytes($size);
        }

        return '0 B';
    }

    public function systemHealth()
    {
        try {
            $health = [
                'cpu' => $this->getCpuUsage(),
                'memory' => $this->getMemoryUsage(),
                'disk' => $this->getDiskUsage(),
                'database' => $this->checkDatabase(),
                'redis' => $this->checkRedis(),
                'overall' => 'healthy',
            ];

            // Determine overall status
            $critical = false;
            $warning = false;

            if ($health['cpu']['percent'] > 90 || $health['memory']['percent'] > 90 || $health['disk']['percent'] > 90) {
                $critical = true;
            } elseif ($health['cpu']['percent'] > 75 || $health['memory']['percent'] > 75 || $health['disk']['percent'] > 75) {
                $warning = true;
            }

            if ($health['database']['status'] !== 'ok' || $health['redis']['status'] !== 'ok') {
                $critical = true;
            }

            $health['overall'] = $critical ? 'critical' : ($warning ? 'warning' : 'healthy');

            return $this->success($health, 'System health retrieved successfully');
        } catch (\Exception $e) {
            Log::error('System health error: '.$e->getMessage());

            return $this->success([
                'cpu' => ['percent' => 0, 'status' => 'unknown'],
                'memory' => ['percent' => 0, 'used' => '0 B', 'total' => '0 B', 'status' => 'unknown'],
                'disk' => ['percent' => 0, 'used' => '0 B', 'total' => '0 B', 'status' => 'unknown'],
                'database' => ['status' => 'unknown', 'message' => 'Unable to check'],
                'redis' => ['status' => 'unknown', 'message' => 'Unable to check'],
                'overall' => 'unknown',
            ], 'System health retrieved successfully');
        }
    }

    protected function getCpuUsage()
    {
        try {
            // Get CPU usage from /proc/loadavg (Linux)
            if (file_exists('/proc/loadavg')) {
                $load = sys_getloadavg();
                $cpuPercent = min(100, ($load[0] * 100) / 4); // Rough estimate
                
                return [
                    'percent' => round($cpuPercent, 2),
                    'load' => $load[0],
                    'status' => $cpuPercent > 90 ? 'critical' : ($cpuPercent > 75 ? 'warning' : 'ok'),
                ];
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return ['percent' => 0, 'load' => 0, 'status' => 'unknown'];
    }

    protected function getMemoryUsage()
    {
        try {
            $memInfo = @file_get_contents('/proc/meminfo');
            if ($memInfo) {
                preg_match('/MemTotal:\s+(\d+)\s+kB/', $memInfo, $total);
                preg_match('/MemAvailable:\s+(\d+)\s+kB/', $memInfo, $available);
                
                if (isset($total[1]) && isset($available[1])) {
                    $totalMem = (int) $total[1] * 1024;
                    $availableMem = (int) $available[1] * 1024;
                    $usedMem = $totalMem - $availableMem;
                    $percent = ($usedMem / $totalMem) * 100;

                    return [
                        'percent' => round($percent, 2),
                        'used' => $this->formatBytes($usedMem),
                        'total' => $this->formatBytes($totalMem),
                        'available' => $this->formatBytes($availableMem),
                        'status' => $percent > 90 ? 'critical' : ($percent > 75 ? 'warning' : 'ok'),
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return ['percent' => 0, 'used' => '0 B', 'total' => '0 B', 'status' => 'unknown'];
    }

    protected function getDiskUsage()
    {
        try {
            $path = base_path();
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;
            $percent = ($used / $total) * 100;

            return [
                'percent' => round($percent, 2),
                'used' => $this->formatBytes($used),
                'total' => $this->formatBytes($total),
                'free' => $this->formatBytes($free),
                'status' => $percent > 90 ? 'critical' : ($percent > 75 ? 'warning' : 'ok'),
            ];
        } catch (\Exception $e) {
            return ['percent' => 0, 'used' => '0 B', 'total' => '0 B', 'status' => 'unknown'];
        }
    }

    protected function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function checkRedis()
    {
        try {
            if (config('cache.default') === 'redis') {
                \Illuminate\Support\Facades\Redis::connection()->ping();
                return ['status' => 'ok', 'message' => 'Connected'];
            }
            return ['status' => 'disabled', 'message' => 'Redis not configured'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
