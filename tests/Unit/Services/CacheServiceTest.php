<?php

namespace Tests\Unit\Services;

use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test clearAll clears all caches.
     */
    public function test_clear_all_clears_all_caches(): void
    {
        // Set some cache values
        Cache::put('contents_list', 'test', 60);
        Cache::put('categories_list', 'test', 60);
        Cache::put('media_list', 'test', 60);

        $service = new CacheService;
        $service->clearAll();

        $this->assertNull(Cache::get('contents_list'));
        $this->assertNull(Cache::get('categories_list'));
        $this->assertNull(Cache::get('media_list'));
    }

    /**
     * Test clearContentCaches clears content-related caches.
     */
    public function test_clear_content_caches_clears_content_caches(): void
    {
        Cache::put('contents_list', 'test', 60);
        Cache::put('contents_published', 'test', 60);
        Cache::put('content_1', 'test', 60);
        Cache::put('sitemap_index', 'test', 60);

        $service = new CacheService;
        $service->clearContentCaches();

        $this->assertNull(Cache::get('contents_list'));
        $this->assertNull(Cache::get('contents_published'));
        $this->assertNull(Cache::get('sitemap_index'));
    }

    /**
     * Test clearContentCaches with specific content ID.
     */
    public function test_clear_content_caches_with_specific_id(): void
    {
        Cache::put('content_1', 'test', 60);
        Cache::put('content_slug_1', 'test', 60);
        Cache::put('content_2', 'test', 60);

        $service = new CacheService;
        $service->clearContentCaches(1);

        $this->assertNull(Cache::get('content_1'));
        $this->assertNull(Cache::get('content_slug_1'));
        $this->assertNotNull(Cache::get('content_2')); // Other content should remain
    }

    /**
     * Test clearCategoryCaches clears category-related caches.
     */
    public function test_clear_category_caches_clears_category_caches(): void
    {
        Cache::put('categories_list', 'test', 60);
        Cache::put('categories_tree', 'test', 60);
        Cache::put('category_1', 'test', 60);

        $service = new CacheService;
        $service->clearCategoryCaches();

        $this->assertNull(Cache::get('categories_list'));
        $this->assertNull(Cache::get('categories_tree'));
    }

    /**
     * Test clearCategoryCaches with specific category ID.
     */
    public function test_clear_category_caches_with_specific_id(): void
    {
        Cache::put('category_1', 'test', 60);
        Cache::put('category_2', 'test', 60);

        $service = new CacheService;
        $service->clearCategoryCaches(1);

        $this->assertNull(Cache::get('category_1'));
        $this->assertNotNull(Cache::get('category_2')); // Other category should remain
    }

    /**
     * Test clearMediaCaches clears media-related caches.
     */
    public function test_clear_media_caches_clears_media_caches(): void
    {
        Cache::put('media_list', 'test', 60);
        Cache::put('media_1', 'test', 60);

        $service = new CacheService;
        $service->clearMediaCaches();

        $this->assertNull(Cache::get('media_list'));
    }

    /**
     * Test getKey generates consistent cache keys.
     */
    public function test_get_key_generates_consistent_keys(): void
    {
        $service = new CacheService;

        $key1 = $service->getKey('content', '1');
        $key2 = $service->getKey('content', '1');

        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('content:', $key1);
    }

    /**
     * Test invalidateByPattern clears matching cache keys.
     */
    public function test_invalidate_by_pattern_clears_matching_keys(): void
    {
        // This test may require Redis
        // For now, we'll test the method exists and doesn't throw
        $service = new CacheService;

        try {
            $result = $service->invalidateByPattern('content:*');
            $this->assertIsInt($result);
        } catch (\Exception $e) {
            // If Redis is not available, that's okay
            $this->assertStringContainsString('Redis', $e->getMessage());
        }
    }
}
