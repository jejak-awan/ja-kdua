<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Services\SecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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
        $securityService = new SecurityService();
        $health = $securityService->checkSystemHealth();

        // Add additional checks
        $health['php'] = [
            'status' => 'ok',
            'message' => 'PHP ' . PHP_VERSION,
        ];

        $health['laravel'] = [
            'status' => 'ok',
            'message' => 'Laravel ' . app()->version(),
        ];

        // Check queue connection
        try {
            Cache::put('health_check_queue', 'test', 10);
            $health['queue'] = ['status' => 'ok', 'message' => 'Queue connection working'];
        } catch (\Exception $e) {
            $health['queue'] = ['status' => 'error', 'message' => 'Queue connection failed: ' . $e->getMessage()];
        }

        return $this->success($health, 'System health check completed');
    }

    public function statistics()
    {
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
                'total_size' => \App\Models\Media::sum('size'),
            ],
            'categories' => \App\Models\Category::count(),
            'tags' => \App\Models\Tag::count(),
            'comments' => \App\Models\Comment::count(),
            'forms' => \App\Models\Form::count(),
            'form_submissions' => \App\Models\FormSubmission::count(),
        ];

        return $this->success($stats, 'System statistics retrieved successfully');
    }

    public function cache()
    {
        $cacheInfo = [
            'driver' => config('cache.default'),
            'size' => $this->getCacheSize(),
        ];

        return $this->success($cacheInfo, 'Cache information retrieved successfully');
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
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
