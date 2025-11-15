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

        if ($categoryId) {
            Cache::forget("category_{$categoryId}");
        }
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
     */
    public function warmUp()
    {
        // Pre-cache frequently accessed data
        Category::where('is_active', true)->get();
        Content::where('status', 'published')->limit(20)->get();
    }
}
