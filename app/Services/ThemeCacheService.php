<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Theme;

/**
 * Service for theme caching operations
 */
class ThemeCacheService
{
    /**
     * Cache key prefixes
     */
    const PREFIX_ACTIVE = 'theme.active.';
    const PREFIX_SETTINGS = 'theme.settings.';
    const PREFIX_ASSETS = 'theme.assets.';
    const PREFIX_MANIFEST = 'theme.manifest.';
    const PREFIX_TEMPLATES = 'theme.templates.';
    const PREFIX_PARTIALS = 'theme.partials.';
    const PREFIX_LAYOUTS = 'theme.layouts.';
    const PREFIX_WIDGETS = 'theme.widgets.';
    const PREFIX_SHORTCODES = 'theme.shortcodes.';

    /**
     * Cache TTL (Time To Live) in seconds
     */
    const TTL_SHORT = 300;      // 5 minutes
    const TTL_MEDIUM = 1800;    // 30 minutes
    const TTL_LONG = 3600;      // 1 hour
    const TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Get active theme with cache
     */
    public function getActiveTheme(string $type = 'frontend', callable $callback = null)
    {
        $cacheKey = self::PREFIX_ACTIVE . $type;

        return Cache::remember($cacheKey, self::TTL_LONG, function () use ($callback, $type) {
            if ($callback) {
                return $callback();
            }
            return null;
        });
    }

    /**
     * Check if cache tags are supported
     */
    protected function tagsSupported(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached']);
    }

    /**
     * Cache theme settings
     */
    public function rememberSettings(Theme $theme, callable $callback)
    {
        $cacheKey = self::PREFIX_SETTINGS . $theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_LONG,
                $callback
            );
        }

        return Cache::remember($cacheKey, self::TTL_LONG, $callback);
    }

    /**
     * Cache theme assets
     */
    public function rememberAssets(Theme $theme, callable $callback)
    {
        $cacheKey = self::PREFIX_ASSETS . $theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_VERY_LONG,
                $callback
            );
        }

        return Cache::remember($cacheKey, self::TTL_VERY_LONG, $callback);
    }

    /**
     * Cache theme manifest
     */
    public function rememberManifest(Theme $theme, callable $callback)
    {
        $cacheKey = self::PREFIX_MANIFEST . $theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_VERY_LONG,
                $callback
            );
        }

        return Cache::remember($cacheKey, self::TTL_VERY_LONG, $callback);
    }

    /**
     * Cache template list
     */
    public function rememberTemplates(Theme $theme, callable $callback)
    {
        $cacheKey = self::PREFIX_TEMPLATES . $theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_LONG,
                $callback
            );
        }

        return Cache::remember($cacheKey, self::TTL_LONG, $callback);
    }

    /**
     * Cache partial list
     */
    public function rememberPartials(Theme $theme, callable $callback)
    {
        $cacheKey = self::PREFIX_PARTIALS . $theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_LONG,
                $callback
            );
        }

        return Cache::remember($cacheKey, self::TTL_LONG, $callback);
    }

    /**
     * Cache layout list
     */
    public function rememberLayouts(Theme $theme, callable $callback)
    {
        $cacheKey = self::PREFIX_LAYOUTS . $theme->id;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', "theme.{$theme->id}"])->remember(
                $cacheKey,
                self::TTL_LONG,
                $callback
            );
        }

        return Cache::remember($cacheKey, self::TTL_LONG, $callback);
    }

    /**
     * Cache widget area
     */
    public function rememberWidgetArea(string $location, callable $callback)
    {
        $cacheKey = self::PREFIX_WIDGETS . $location;

        if ($this->tagsSupported()) {
            return Cache::tags(['theme', 'widgets', "widgets.{$location}"])->remember(
                $cacheKey,
                self::TTL_MEDIUM,
                $callback
            );
        }

        return Cache::remember($cacheKey, self::TTL_MEDIUM, $callback);
    }

    /**
     * Cache processed shortcodes
     */
    public function rememberShortcode(string $content, callable $callback)
    {
        $cacheKey = self::PREFIX_SHORTCODES . md5($content);

        return Cache::remember($cacheKey, self::TTL_MEDIUM, $callback);
    }

    /**
     * Clear theme cache
     */
    public function clearTheme(Theme $theme): void
    {
        try {
            if ($this->tagsSupported()) {
                // Clear specific theme caches using tags
                Cache::tags(["theme.{$theme->id}"])->flush();
            } else {
                // Fallback: clear individual cache keys
                Cache::forget(self::PREFIX_SETTINGS . $theme->id);
                Cache::forget(self::PREFIX_ASSETS . $theme->id);
                Cache::forget(self::PREFIX_MANIFEST . $theme->id);
                Cache::forget(self::PREFIX_TEMPLATES . $theme->id);
                Cache::forget(self::PREFIX_PARTIALS . $theme->id);
                Cache::forget(self::PREFIX_LAYOUTS . $theme->id);
            }

            // Clear active theme cache for all types
            foreach (['frontend', 'admin', 'email'] as $type) {
                Cache::forget(self::PREFIX_ACTIVE . $type);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear theme cache: ' . $e->getMessage());
        }
    }

    /**
     * Clear all theme caches
     */
    public function clearAll(): void
    {
        try {
            if ($this->tagsSupported()) {
                Cache::tags(['theme'])->flush();
            } else {
                // Fallback: clear all cache (less efficient but works)
                Cache::flush();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear all theme caches: ' . $e->getMessage());
        }
    }

    /**
     * Clear widget cache
     */
    public function clearWidgets(?string $location = null): void
    {
        try {
            if ($this->tagsSupported()) {
                if ($location) {
                    Cache::tags(["widgets.{$location}"])->flush();
                } else {
                    Cache::tags(['widgets'])->flush();
                }
            } else {
                // Fallback: clear widget cache by key
                if ($location) {
                    Cache::forget(self::PREFIX_WIDGETS . $location);
                } else {
                    // Can't efficiently clear all widgets without tags, so just log
                    Log::info('Widget cache clear requested but tags not supported');
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear widget cache: ' . $e->getMessage());
        }
    }

    /**
     * Clear cache by prefix (fallback for non-tagged cache)
     */
    protected function clearByPrefix(string $prefix): void
    {
        // This is a fallback method
        // In production with Redis, use tags instead
        Log::info('Clearing cache by prefix: ' . $prefix);
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'tags_supported' => config('cache.default') === 'redis',
            'ttl_short' => self::TTL_SHORT,
            'ttl_medium' => self::TTL_MEDIUM,
            'ttl_long' => self::TTL_LONG,
            'ttl_very_long' => self::TTL_VERY_LONG,
        ];
    }
}

