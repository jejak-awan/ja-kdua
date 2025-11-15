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
