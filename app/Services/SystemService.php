<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemService
{
    /**
     * Get system information
     */
    public function getSystemInfo(): array
    {
        $memoryInfo = $this->getMemoryUsage();
        $diskInfo = $this->getDiskUsage();
        
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => DB::connection()->getDatabaseName(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
            // Memory usage info
            'memory_usage' => $memoryInfo['used'] ?? null,
            'memory_usage_percent' => $memoryInfo['percent'] ?? 0,
            'memory_total' => $memoryInfo['total'] ?? null,
            // Disk usage info
            'disk_usage' => [
                'used' => $diskInfo['used'] ?? '0 B',
                'total' => $diskInfo['total'] ?? '0 B',
                'percent' => $diskInfo['percent'] ?? 0,
            ],
            'disk_usage_percent' => $diskInfo['percent'] ?? 0,
            // Uptime
            'uptime' => $this->getUptime(),
        ];
    }

    /**
     * Get application statistics
     */
    public function getStatistics(): array
    {
        try {
            // Get page views/visits count (if analytics exists)
            $totalVisits = 0;
            try {
                if (class_exists(\App\Models\PageView::class)) {
                    $totalVisits = \App\Models\PageView::count();
                } elseif (class_exists(\App\Models\Analytics::class)) {
                    $totalVisits = \App\Models\Analytics::count();
                }
            } catch (\Exception $e) {
                // Visits tracking not available
            }

            return [
                // Flat keys for frontend compatibility
                'total_contents' => \App\Models\Content::count(),
                'total_users' => \App\Models\User::count(),
                'total_media' => \App\Models\Media::count(),
                'total_visits' => $totalVisits,
                // Detailed nested data (for extended use)
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
        } catch (\Exception $e) {
            Log::error('System statistics error: ' . $e->getMessage());
            return $this->getDefaultStatistics();
        }
    }

    /**
     * Get default statistics on error
     */
    protected function getDefaultStatistics(): array
    {
        return [
            'total_contents' => 0,
            'total_users' => 0,
            'total_media' => 0,
            'total_visits' => 0,
            'contents' => ['total' => 0, 'published' => 0, 'draft' => 0],
            'users' => ['total' => 0, 'verified' => 0],
            'media' => ['total' => 0, 'total_size' => 0],
            'categories' => 0,
            'tags' => 0,
            'comments' => 0,
            'forms' => 0,
            'form_submissions' => 0,
        ];
    }

    /**
     * Get system uptime
     */
    public function getUptime(): ?int
    {
        try {
            // Try reading from /proc/uptime (Linux)
            if (file_exists('/proc/uptime')) {
                $uptime = file_get_contents('/proc/uptime');
                $uptime = explode(' ', $uptime);
                return (int) $uptime[0];
            }
            
            // Fallback: try uptime command
            $output = @shell_exec('uptime -s 2>/dev/null');
            if ($output) {
                $bootTime = strtotime(trim($output));
                if ($bootTime) {
                    return time() - $bootTime;
                }
            }
        } catch (\Exception $e) {
            Log::debug('Uptime error: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get comprehensive system health
     */
    public function getSystemHealth(): array
    {
        $health = [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'overall' => 'healthy',
        ];

        // Determine overall status
        $critical = ($health['cpu']['percent'] > 90 || $health['memory']['percent'] > 90 || $health['disk']['percent'] > 90);
        $warning = ($health['cpu']['percent'] > 75 || $health['memory']['percent'] > 75 || $health['disk']['percent'] > 75);

        if ($health['database']['status'] !== 'ok' || $health['redis']['status'] !== 'ok') {
            $critical = true;
        }

        $health['overall'] = $critical ? 'critical' : ($warning ? 'warning' : 'healthy');

        return $health;
    }

    /**
     * Get CPU usage
     */
    public function getCpuUsage(): array
    {
        try {
            if (file_exists('/proc/loadavg')) {
                $load = sys_getloadavg();
                
                // Determine number of cores
                $cores = 1;
                if (file_exists('/proc/cpuinfo')) {
                    $cpuinfo = file_get_contents('/proc/cpuinfo');
                    $cores = substr_count($cpuinfo, 'processor');
                }
                if ($cores < 1) $cores = 1;

                $cpuPercent = min(100, ($load[0] * 100) / $cores);

                return [
                    'percent' => round($cpuPercent, 2),
                    'load' => $load[0],
                    'cores' => $cores,
                    'status' => $cpuPercent > 90 ? 'critical' : ($cpuPercent > 75 ? 'warning' : 'ok'),
                ];
            }
        } catch (\Exception $e) {
            Log::debug('CPU usage error: ' . $e->getMessage());
        }

        return ['percent' => 0, 'load' => 0, 'status' => 'unknown'];
    }

    /**
     * Get memory usage
     */
    public function getMemoryUsage(): array
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
            Log::debug('Memory usage error: ' . $e->getMessage());
        }

        return ['percent' => 0, 'used' => '0 B', 'total' => '0 B', 'status' => 'unknown'];
    }

    /**
     * Get disk usage
     */
    public function getDiskUsage(): array
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

    /**
     * Check database connection
     */
    public function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check Redis connection
     */
    public function checkRedis(): array
    {
        try {
            // Check if Redis class exists and connection is configured
            if (class_exists(\Illuminate\Support\Facades\Redis::class)) {
                try {
                    $redis = \Illuminate\Support\Facades\Redis::connection();
                    $redis->ping();
                    return ['status' => 'ok', 'message' => 'Connected'];
                } catch (\Exception $e) {
                    // Only return error if it's actually configured but failing
                    // If not configured (e.g. standard file cache), return disabled
                     if (config('database.redis.default.host')) {
                         return ['status' => 'error', 'message' => 'Connection Failed: ' . $e->getMessage()];
                     }
                }
            }
            return ['status' => 'disabled', 'message' => 'Redis not configured'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get cache size and count
     */
    public function getCacheStats(string $driver = 'file'): array
    {
        if ($driver === 'file') {
            $cachePath = storage_path('framework/cache');
            if (is_dir($cachePath)) {
                $size = 0;
                $count = 0;
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cachePath)) as $file) {
                    if ($file->isFile()) {
                        $size += $file->getSize();
                        $count++;
                    }
                }
                return ['size' => $this->formatBytes($size), 'count' => $count];
            }
            return ['size' => '0 B', 'count' => 0];
        }

        return ['size' => '0 B', 'count' => 0];
    }

    /**
     * Get cache size (backward compatibility)
     */
    public function getCacheSize(): string
    {
        return $this->getCacheStats('file')['size'];
    }

    /**
     * Get cache status with stats
     */
    public function getCacheStatus(): array
    {
        $driver = config('cache.default', 'file');
        $hits = 0;
        $misses = 0;
        $keys = 0;
        $enabled = false;
        $size = '0 B';

        if ($driver === 'redis') {
            try {
                $redis = \Illuminate\Support\Facades\Redis::connection();
                $redis->ping();
                $enabled = true;
                
                $info = $redis->info('stats');
                $hits = $info['keyspace_hits'] ?? 0;
                $misses = $info['keyspace_misses'] ?? 0;
                $keys = $redis->dbsize();
                
                // Estimate size for Redis (not accurate but better than nothing)
                $memory = $redis->info('memory');
                $size = $this->formatBytes($memory['used_memory'] ?? 0);

            } catch (\Exception $e) {
                Log::debug('Redis stats not available: ' . $e->getMessage());
                // Try to get file cache size as fallback if redis fails but driver is set
                // Or leave as 0
            }
        } elseif ($driver === 'file') {
            $enabled = true; // File driver is always active
            $stats = $this->getCacheStats('file');
            $size = $stats['size'];
            $keys = $stats['count'];
        } else {
            // Other drivers (database, etc)
            $enabled = true; // Assume active if config exists
            try {
                if ($driver === 'database') {
                    $keys = DB::table(config('cache.stores.database.table', 'cache'))->count();
                }
            } catch (\Exception $e) {
                $enabled = false;
            }
        }

        return [
            'status' => $enabled ? 'Active' : 'Inactive',
            'enabled' => $enabled,
            'driver' => $driver,
            'hits' => $hits,
            'misses' => $misses,
            'keys' => $keys,
            'size' => $size,
        ];
    }

    /**
     * Get database size information
     */
    public function getDatabaseSize(): array
    {
        try {
            $database = DB::connection()->getDatabaseName();
            $size = DB::select("SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = ?", [$database]);

            return [
                'total_mb' => $size[0]->size_mb ?? 0,
                'formatted' => $this->formatBytes(($size[0]->size_mb ?? 0) * 1024 * 1024),
            ];
        } catch (\Exception $e) {
            return ['total_mb' => 0, 'formatted' => '0 B'];
        }
    }

    /**
     * Get table statistics
     */
    public function getTableStatistics(): array
    {
        try {
            $database = DB::connection()->getDatabaseName();
            $tables = DB::select("SELECT 
                table_name,
                ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                table_rows
                FROM information_schema.TABLES 
                WHERE table_schema = ?
                ORDER BY (data_length + index_length) DESC
                LIMIT 10", [$database]);

            return array_map(function ($table) {
                return [
                    'name' => $table->table_name,
                    'size_mb' => $table->size_mb,
                    'rows' => $table->table_rows,
                    'formatted_size' => $this->formatBytes($table->size_mb * 1024 * 1024),
                ];
            }, $tables);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Format bytes to human readable
     */
    public function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
