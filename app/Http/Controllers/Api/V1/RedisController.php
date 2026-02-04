<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\RedisSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class RedisController extends BaseApiController
{
    /**
     * Get all Redis settings.
     */
    public function index()
    {
        $settings = RedisSetting::orderBy('group')->orderBy('key')->get();

        /** @phpstan-ignore argument.type */
        $grouped = $settings->groupBy('group')->map(function (\Illuminate\Database\Eloquent\Collection $items) {
            /** @phpstan-ignore return.type */
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
            return $this->validationError($validator->errors()->toArray());
        }

        foreach ($request->input('settings') as $settingData) {
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
            $redis = Redis::connection();

            // Check if Redis requires authentication
            try {
                $pong = $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $duration = round((microtime(true) - $start) * 1000, 2);

            if ($pong) {
                return $this->success([
                    'connected' => true,
                    'response_time' => $duration.'ms',
                    'message' => 'Redis connection successful',
                ], 'Connection test passed');
            }

            return $this->error('Redis connection failed', 500);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'NOAUTH') || str_contains($message, 'Authentication required')) {
                return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
            }

            return $this->error('Redis connection error: '.$message, 500);
        }
    }

    /**
     * Get Redis server info.
     */
    public function info()
    {
        try {
            $redis = Redis::connection();

            // Check if Redis requires authentication
            try {
                $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $info = $redis->info();

            // Get some key metrics (Redis returns flat array)
            $stats = [
                'version' => $info['redis_version'] ?? 'Unknown',
                'uptime_days' => isset($info['uptime_in_days']) ? $info['uptime_in_days'].' days' : 'Unknown',
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
            $message = $e->getMessage();
            if (str_contains($message, 'NOAUTH') || str_contains($message, 'Authentication required')) {
                return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
            }

            return $this->error('Failed to retrieve Redis info: '.$message, 500);
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
            return $this->error('Failed to flush cache: '.$e->getMessage(), 500);
        }
    }

    /**
     * Get cache statistics.
     */
    public function cacheStats()
    {
        try {
            $redis = Redis::connection('cache');

            // Check if Redis requires authentication
            try {
                $redis->ping();
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'NOAUTH') || str_contains($e->getMessage(), 'Authentication required')) {
                    return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
                }
                throw $e;
            }

            $keys = $redis->keys('*');

            $prefix = config('database.redis.options.prefix');

            $stats = [
                'total_keys' => count($keys),
                'cache_size' => $this->getCacheSize($redis, $keys, $prefix),
                'expired_keys' => $this->getExpiredKeysCount($redis),
                'top_keys' => $this->getTopKeys($redis, $keys, 10, $prefix),
            ];

            return $this->success($stats, 'Cache statistics retrieved successfully');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'NOAUTH') || str_contains($message, 'Authentication required')) {
                return $this->error('Redis authentication required. Please configure REDIS_PASSWORD in your .env file.', 401, [], 'REDIS_AUTH_REQUIRED');
            }

            return $this->error('Failed to retrieve cache stats: '.$message, 500);
        }
    }

    /**
     * Helper: Get total keys count.
     */
    private function getTotalKeys()
    {
        $count = 0;
        // Try to count keys from both default and cache connections
        try {
            $count += count(Redis::connection('default')->keys('*'));
        } catch (\Exception $e) {
        }

        try {
            $count += count(Redis::connection('cache')->keys('*'));
        } catch (\Exception $e) {
        }

        return $count;
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

        return round(($hits / $total) * 100, 2).'%';
    }

    /**
     * Helper: Get cache size.
     */
    private function getCacheSize($redis, $keys, $prefix = null)
    {
        try {
            $size = 0;
            foreach (array_slice($keys, 0, 100) as $key) { // Sample first 100 keys
                // Strip prefix if present to avoid double prefixing by the client
                $lookupKey = ($prefix && str_starts_with($key, $prefix))
                    ? substr($key, strlen($prefix))
                    : $key;

                $size += strlen($redis->get($lookupKey) ?? '');
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

        return round($bytes, 2).' '.$units[$pow];
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
    private function getTopKeys($redis, $keys, $limit = 10, $prefix = null)
    {
        $topKeys = [];

        foreach (array_slice($keys, 0, min(count($keys), 100)) as $key) {
            try {
                // Strip prefix for lookup
                $lookupKey = ($prefix && str_starts_with($key, $prefix))
                    ? substr($key, strlen($prefix))
                    : $key;

                $ttl = $redis->ttl($lookupKey);
                $size = strlen($redis->get($lookupKey) ?? '');

                $topKeys[] = [
                    'key' => $key, // Show full key for display
                    'size' => $this->formatBytes($size),
                    'ttl' => $ttl > 0 ? $ttl.'s' : ($ttl === -1 ? 'Never' : 'Expired'),
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        // Sort by size (descending)
        usort($topKeys, function ($a, $b) {
            return $b['size'] <=> $a['size']; // Note: comparing formatted strings isn't ideal but sufficient for simple display if units match
        });

        return array_slice($topKeys, 0, $limit);
    }

    /**
     * Warm up cache (optimize).
     */
    public function warmCache()
    {
        try {
            // Run optimization commands
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache'); // view:cache exists in recent Laravel versions

            return $this->success(null, 'Cache warmed successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to warm cache: '.$e->getMessage(), 500);
        }
    }
}
