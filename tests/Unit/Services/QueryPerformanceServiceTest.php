<?php

namespace Tests\Unit\Services;

use App\Services\QueryPerformanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QueryPerformanceServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test enableQueryLog enables query logging.
     */
    public function test_enable_query_log_enables_logging(): void
    {
        $service = new QueryPerformanceService();
        $service->enableQueryLog();

        // Make a query
        DB::table('users')->count();

        $queries = $service->getQueryLog();
        $this->assertNotEmpty($queries);
    }

    /**
     * Test getQueryLog returns query log.
     */
    public function test_get_query_log_returns_query_log(): void
    {
        $service = new QueryPerformanceService();
        $service->enableQueryLog();

        DB::table('users')->count();

        $queries = $service->getQueryLog();
        $this->assertIsArray($queries);
    }

    /**
     * Test analyzeQueries analyzes query performance.
     */
    public function test_analyze_queries_analyzes_performance(): void
    {
        $service = new QueryPerformanceService();
        $service->enableQueryLog();

        DB::table('users')->count();
        DB::table('users')->count(); // Duplicate query

        $analysis = $service->analyzeQueries();

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('total_queries', $analysis);
        $this->assertArrayHasKey('total_time', $analysis);
        $this->assertArrayHasKey('slow_queries', $analysis);
        $this->assertArrayHasKey('duplicate_queries', $analysis);
        $this->assertArrayHasKey('n_plus_one_candidates', $analysis);
    }

    /**
     * Test getPerformanceStats returns statistics.
     */
    public function test_get_performance_stats_returns_statistics(): void
    {
        $service = new QueryPerformanceService();
        $service->enableQueryLog();

        DB::table('users')->count();

        $stats = $service->getPerformanceStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_queries', $stats);
        $this->assertArrayHasKey('total_time', $stats);
        $this->assertArrayHasKey('average_time', $stats);
        $this->assertArrayHasKey('slow_queries_count', $stats);
    }

    /**
     * Test cacheMetrics caches metrics.
     */
    public function test_cache_metrics_caches_metrics(): void
    {
        $service = new QueryPerformanceService();
        $metrics = ['test' => 'data'];

        $service->cacheMetrics('test_key', $metrics, 60);

        $cached = $service->getCachedMetrics('test_key');
        $this->assertEquals($metrics, $cached);
    }

    /**
     * Test getCachedMetrics returns null for non-existent key.
     */
    public function test_get_cached_metrics_returns_null_for_non_existent(): void
    {
        $service = new QueryPerformanceService();
        $cached = $service->getCachedMetrics('non_existent_key');

        $this->assertNull($cached);
    }
}

