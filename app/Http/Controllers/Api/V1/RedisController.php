<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\RedisSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class RedisController extends BaseApiController
{
    /**
     * Get all Redis settings.
     */
    public function index()
    {
        $settings = RedisSetting::orderBy('group')->orderBy('key')->get();

        $grouped = $settings->groupBy('group')->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'key' => $item->key,
                    'value' => $item->value,
                    'type' => $item->type,
                    'description' => $item->description,
                    'is_encrypted' => $item->is_encrypted,
                ];
            });
        });

        return $this->success($grouped, 'Redis settings retrieved successfully');
    }

    /**
     * Update Redis settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        foreach ($request->settings as $settingData) {
            RedisSetting::setValue($settingData['key'], $settingData['value']);
        }

        // Clear config cache to apply new settings
        Artisan::call('config:clear');

        return $this->success(null, 'Redis settings updated successfully');
    }

    /**
     * Test Redis connection.
     */
    public function testConnection()
    {
        try {
            $start = microtime(true);
            $pong = Redis::connection()->ping();
            $duration = round((microtime(true) - $start) * 1000, 2);

            if ($pong) {
                return $this->success([
                    'connected' => true,
                    'response_time' => $duration . 'ms',
                    'message' => 'Redis connection successful',
                ], 'Connection test passed');
            }

            return $this->error('Redis connection failed', 500);
        } catch (\Exception $e) {
            return $this->error('Redis connection error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get Redis server info.
     */
    public function info()
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();

            // Get some key metrics (Redis returns flat array)
            $stats = [
                'version' => $info['redis_version'] ?? 'Unknown',
                'uptime_days' => isset($info['uptime_in_days']) ? $info['uptime_in_days'] . ' days' : 'Unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory' => $info['used_memory_human'] ?? 'Unknown',
                'total_keys' => $this->getTotalKeys(),
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate($info),
                'total_commands' => $info['total_commands_processed'] ?? 0,
                'operations_per_sec' => $info['instantaneous_ops_per_sec'] ?? 0,
            ];

            return $this->success($stats, 'Redis info retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve Redis info: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Flush Redis cache.
     */
    public function flushCache(Request $request)
    {
        try {
            $type = $request->input('type', 'all'); // all, cache, config, route, view

            switch ($type) {
                case 'cache':
                    Artisan::call('cache:clear');
                    break;
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'all':
                default:
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Redis::connection()->flushdb();
                    break;
            }

            return $this->success(null, 'Cache cleared successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to flush cache: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get cache statistics.
     */
    public function cacheStats()
    {
        try {
            $redis = Redis::connection();
            $keys = $redis->keys('*');

            $stats = [
                'total_keys' => count($keys),
                'cache_size' => $this->getCacheSize($redis, $keys),
                'expired_keys' => $this->getExpiredKeysCount($redis),
                'top_keys' => $this->getTopKeys($redis, $keys, 10),
            ];

            return $this->success($stats, 'Cache statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve cache stats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Helper: Get total keys count.
     */
    private function getTotalKeys()
    {
        try {
            $redis = Redis::connection();
            $keys = $redis->keys('*');

            return count($keys);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Helper: Calculate hit rate.
     */
    private function calculateHitRate($info)
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        if ($total === 0) {
            return '0%';
        }

        return round(($hits / $total) * 100, 2) . '%';
    }

    /**
     * Helper: Get cache size.
     */
    private function getCacheSize($redis, $keys)
    {
        try {
            $size = 0;
            foreach (array_slice($keys, 0, 100) as $key) { // Sample first 100 keys
                $size += strlen($redis->get($key) ?? '');
            }

            return $this->formatBytes($size);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Helper: Format bytes to human readable.
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Helper: Get expired keys count.
     */
    private function getExpiredKeysCount($redis)
    {
        try {
            $info = $redis->info();

            return $info['expired_keys'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Helper: Get top keys.
     */
    private function getTopKeys($redis, $keys, $limit = 10)
    {
        $topKeys = [];

        foreach (array_slice($keys, 0, min(count($keys), 100)) as $key) {
            try {
                $ttl = $redis->ttl($key);
                $size = strlen($redis->get($key) ?? '');

                $topKeys[] = [
                    'key' => $key,
                    'size' => $this->formatBytes($size),
                    'ttl' => $ttl > 0 ? $ttl . 's' : ($ttl === -1 ? 'Never' : 'Expired'),
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        // Sort by size (descending)
        usort($topKeys, function ($a, $b) {
            return $b['size'] <=> $a['size'];
        });

        return array_slice($topKeys, 0, $limit);
    }
}
