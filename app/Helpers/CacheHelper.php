<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Cache Helper Class
 *
 * Provides convenient methods for caching operations including
 * content, media, categories, statistics, and API responses.
 * Supports tagged cache for grouped invalidation (Redis only).
 *
 * @package App\Helpers
 */
class CacheHelper
{
    /**
     * Cache key prefixes
     */
    const PREFIX_CONTENT = 'content:';
    const PREFIX_MEDIA = 'media:';
    const PREFIX_CATEGORY = 'category:';
    const PREFIX_TAG = 'tag:';
    const PREFIX_USER = 'user:';
    const PREFIX_SETTINGS = 'settings:';
    const PREFIX_STATISTICS = 'statistics:';
    const PREFIX_API = 'api:';

    /**
     * Default cache TTL (Time To Live) in seconds
     */
    const TTL_SHORT = 300;      // 5 minutes
    const TTL_MEDIUM = 1800;    // 30 minutes
    const TTL_LONG = 3600;      // 1 hour
    const TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Get cache key with prefix
     */
    public static function key(string $prefix, string $key): string
    {
        return $prefix . $key;
    }

    /**
     * Cache API response
     */
    public static function rememberApiResponse(string $key, int $ttl, callable $callback)
    {
        $cacheKey = self::key(self::PREFIX_API, $key);
        
        return Cache::remember($cacheKey, $ttl, function () use ($callback) {
            try {
                return $callback();
            } catch (\Exception $e) {
                Log::error('Cache callback error: ' . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Cache content data
     */
    public static function rememberContent(int $id, int $ttl, callable $callback)
    {
        $cacheKey = self::key(self::PREFIX_CONTENT, $id);
        
        return Cache::tags(['content'])->remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache media data
     */
    public static function rememberMedia(int $id, int $ttl, callable $callback)
    {
        $cacheKey = self::key(self::PREFIX_MEDIA, $id);
        
        return Cache::tags(['media'])->remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache category data
     */
    public static function rememberCategory(int $id, int $ttl, callable $callback)
    {
        $cacheKey = self::key(self::PREFIX_CATEGORY, $id);
        
        return Cache::tags(['categories'])->remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache statistics
     */
    public static function rememberStatistics(string $type, int $ttl, callable $callback)
    {
        $cacheKey = self::key(self::PREFIX_STATISTICS, $type);
        
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Invalidate content cache
     */
    public static function invalidateContent(int $id): void
    {
        $cacheKey = self::key(self::PREFIX_CONTENT, $id);
        Cache::forget($cacheKey);
        Cache::tags(['content'])->flush();
    }

    /**
     * Invalidate media cache
     */
    public static function invalidateMedia(int $id): void
    {
        $cacheKey = self::key(self::PREFIX_MEDIA, $id);
        Cache::forget($cacheKey);
        Cache::tags(['media'])->flush();
    }

    /**
     * Invalidate category cache
     */
    public static function invalidateCategory(int $id): void
    {
        $cacheKey = self::key(self::PREFIX_CATEGORY, $id);
        Cache::forget($cacheKey);
        Cache::tags(['categories'])->flush();
    }

    /**
     * Invalidate all content-related cache
     */
    public static function invalidateAllContent(): void
    {
        Cache::tags(['content'])->flush();
    }

    /**
     * Invalidate all media-related cache
     */
    public static function invalidateAllMedia(): void
    {
        Cache::tags(['media'])->flush();
    }

    /**
     * Invalidate all category-related cache
     */
    public static function invalidateAllCategories(): void
    {
        Cache::tags(['categories'])->flush();
    }

    /**
     * Invalidate statistics cache
     */
    public static function invalidateStatistics(string $type = null): void
    {
        if ($type) {
            $cacheKey = self::key(self::PREFIX_STATISTICS, $type);
            Cache::forget($cacheKey);
        } else {
            // Invalidate all statistics
            Cache::flush(); // Note: This clears ALL cache, use with caution
        }
    }

    /**
     * Invalidate API response cache
     */
    public static function invalidateApiResponse(string $key): void
    {
        $cacheKey = self::key(self::PREFIX_API, $key);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all cache
     */
    public static function clearAll(): void
    {
        Cache::flush();
    }

    /**
     * Check if cache is available
     */
    public static function isAvailable(): bool
    {
        try {
            Cache::put('cache_test', 'test', 1);
            $result = Cache::get('cache_test') === 'test';
            Cache::forget('cache_test');
            return $result;
        } catch (\Exception $e) {
            Log::warning('Cache not available: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cache statistics (if supported)
     */
    public static function getStats(): array
    {
        // This is a placeholder - actual implementation depends on cache driver
        return [
            'driver' => config('cache.default'),
            'available' => self::isAvailable(),
        ];
    }
}

