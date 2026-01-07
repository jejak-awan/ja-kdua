<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Content;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Clear all CMS-related caches
     */
    public function clearAll()
    {
        Cache::flush();
        $this->clearContentCaches();
        $this->clearCategoryCaches();
    }

    /**
     * Clear content-related caches
     */
    public function clearContentCaches($contentId = null)
    {
        // Clear content list cache
        Cache::forget('contents_list');
        Cache::forget('contents_published');

        // Clear specific content cache
        if ($contentId) {
            Cache::forget("content_{$contentId}");
            Cache::forget("content_slug_{$contentId}");
        }

        // Clear sitemap cache
        Cache::forget('sitemap_index');
        Cache::forget('sitemap_pages');
        Cache::forget('sitemap_posts');
        Cache::forget('sitemap_categories');
    }

    /**
     * Clear category-related caches
     */
    public function clearCategoryCaches($categoryId = null)
    {
        Cache::forget('categories_list');
        Cache::forget('categories_tree');
        Cache::forget('categories_flat');

        if ($categoryId) {
            Cache::forget("category_{$categoryId}");
        }
    }

    /**
     * Clear tag-related caches
     */
    public function clearTagCaches()
    {
        Cache::forget('tags_all');
        Cache::forget('tags_statistics');
    }

    /**
     * Clear media caches
     */
    public function clearMediaCaches()
    {
        Cache::forget('media_list');
    }

    /**
     * Clear user caches
     */
    public function clearUserCaches($userId = null)
    {
        if ($userId) {
            Cache::forget("user_{$userId}");
            Cache::forget("user_activity_{$userId}");
        }
    }

    /**
     * Clear SEO caches
     */
    public function clearSeoCaches()
    {
        Cache::forget('sitemap_index');
        Cache::forget('sitemap_pages');
        Cache::forget('sitemap_posts');
        Cache::forget('sitemap_categories');
    }

    /**
     * Warm up important caches
     *
     * @deprecated Use CacheWarmingService instead
     */
    public function warmUp()
    {
        // Pre-cache frequently accessed data
        Category::where('is_active', true)->get();
        Content::where('status', 'published')->limit(20)->get();
    }

    /**
     * Get cache key with consistent prefix
     */
    public function getKey(string $prefix, string $key): string
    {
        return $prefix.':'.$key;
    }

    /**
     * Invalidate cache by pattern (Redis only)
     */
    public function invalidateByPattern(string $pattern): int
    {
        if (! $this->isRedisAvailable()) {
            return 0;
        }

        try {
            $redis = Cache::getRedis();
            $keys = $redis->keys(config('cache.prefix').':'.$pattern);

            if (empty($keys)) {
                return 0;
            }

            return $redis->del($keys);
        } catch (\Exception $e) {
            \Log::warning('Failed to invalidate cache by pattern: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * Check if Redis is available and configured
     */
    public function isRedisAvailable(): bool
    {
        $cacheDriver = config('cache.default');

        // Not using Redis
        if (! in_array($cacheDriver, ['redis', 'redis_failover', 'failover'])) {
            return false;
        }

        try {
            $redis = \Illuminate\Support\Facades\Redis::connection();
            $redis->ping();

            return true;
        } catch (\Exception $e) {
            \Log::debug('Redis not available: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get the preferred cache store based on availability
     * Returns 'redis' if available, otherwise 'file'
     */
    public function getPreferredStore(): string
    {
        return $this->isRedisAvailable() ? 'redis' : 'file';
    }

    /**
     * Get current cache driver info
     */
    public function getCacheDriverInfo(): array
    {
        $driver = config('cache.default');
        $redisAvailable = $this->isRedisAvailable();

        return [
            'configured_driver' => $driver,
            'redis_available' => $redisAvailable,
            'effective_driver' => $redisAvailable ? 'redis' : 'file',
            'recommendation' => $redisAvailable
                ? 'Redis is active - optimal performance'
                : 'Using file cache - consider enabling Redis for better performance',
        ];
    }

    /**
     * Smart cache remember - uses Redis if available, falls back to file
     */
    public function smartRemember(string $key, int $ttlSeconds, callable $callback)
    {
        $store = $this->getPreferredStore();

        return Cache::store($store)->remember($key, $ttlSeconds, $callback);
    }
}
