<?php

namespace Tests\Unit\Services;

use App\Models\Content;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Media;
use App\Models\Language;
use App\Services\CacheWarmingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheWarmingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test warmAll warms all cache types.
     */
    public function test_warm_all_warms_all_cache_types(): void
    {
        // Create some test data
        Content::factory()->published()->count(5)->create();
        Category::factory()->count(3)->create();
        Tag::factory()->count(5)->create();
        Media::factory()->count(3)->create();
        Language::factory()->active()->count(2)->create();

        $service = new CacheWarmingService();
        $results = $service->warmAll();

        $this->assertIsArray($results);
        $this->assertArrayHasKey('content', $results);
        $this->assertArrayHasKey('categories', $results);
        $this->assertArrayHasKey('tags', $results);
        $this->assertArrayHasKey('media', $results);
        $this->assertArrayHasKey('languages', $results);
        $this->assertArrayHasKey('statistics', $results);
    }

    /**
     * Test warmContent caches content.
     */
    public function test_warm_content_caches_content(): void
    {
        Content::factory()->published()->count(10)->create();

        $service = new CacheWarmingService();
        $result = $service->warmContent(5);

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test warmCategories caches categories.
     */
    public function test_warm_categories_caches_categories(): void
    {
        Category::factory()->count(5)->create();

        $service = new CacheWarmingService();
        $result = $service->warmCategories();

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test warmTags caches tags.
     */
    public function test_warm_tags_caches_tags(): void
    {
        Tag::factory()->count(5)->create();

        $service = new CacheWarmingService();
        $result = $service->warmTags();

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test warmMedia caches media.
     */
    public function test_warm_media_caches_media(): void
    {
        Media::factory()->count(5)->create();

        $service = new CacheWarmingService();
        $result = $service->warmMedia(5);

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test warmLanguages caches languages.
     */
    public function test_warm_languages_caches_languages(): void
    {
        Language::factory()->active()->count(3)->create();

        $service = new CacheWarmingService();
        $result = $service->warmLanguages();

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test warmStatistics caches statistics.
     */
    public function test_warm_statistics_caches_statistics(): void
    {
        $service = new CacheWarmingService();
        $result = $service->warmStatistics();

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    /**
     * Test warmByType warms specific cache type.
     */
    public function test_warm_by_type_warms_specific_type(): void
    {
        Content::factory()->published()->count(5)->create();

        $service = new CacheWarmingService();
        $result = $service->warmByType('content', 5);

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /**
     * Test getStatistics returns statistics.
     */
    public function test_get_statistics_returns_statistics(): void
    {
        $service = new CacheWarmingService();
        $stats = $service->getStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('last_warmed_at', $stats);
    }
}

