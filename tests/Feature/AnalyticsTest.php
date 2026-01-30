<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisit;
use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    // use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with analytics permission
        $this->admin = $this->createAdminUser();
    }

    /**
     * Test admin can get analytics overview.
     */
    public function test_admin_can_get_analytics_overview(): void
    {
        // Create some test analytics data
        AnalyticsVisit::factory()->count(10)->create([
            'visited_at' => now()->subDays(5),
        ]);

        AnalyticsSession::factory()->count(5)->create([
            'started_at' => now()->subDays(5),
            'ended_at' => now()->subDays(5)->addMinutes(30),
            'duration' => 1800, // 30 minutes in seconds
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/overview');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_visits',
                'unique_visitors',
                'total_sessions',
                'avg_session_duration',
                'bounce_rate',
                'page_views',
            ],
        ]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(10, $data['total_visits']);
        $this->assertGreaterThanOrEqual(5, $data['total_sessions']);
    }

    /**
     * Test admin can get analytics visits grouped by day.
     */
    public function test_admin_can_get_analytics_visits(): void
    {
        // Create visits over multiple days
        AnalyticsVisit::factory()->count(5)->create([
            'visited_at' => now()->subDays(2),
        ]);

        AnalyticsVisit::factory()->count(3)->create([
            'visited_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/visits?group_by=day');

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test admin can get top pages.
     */
    public function test_admin_can_get_top_pages(): void
    {
        // Create visits to different pages
        AnalyticsVisit::factory()->count(10)->create([
            'url' => 'https://example.com/page1',
            'visited_at' => now()->subDays(1),
        ]);

        AnalyticsVisit::factory()->count(5)->create([
            'url' => 'https://example.com/page2',
            'visited_at' => now()->subDays(1),
        ]);

        AnalyticsVisit::factory()->count(3)->create([
            'url' => 'https://example.com/page3',
            'visited_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/top-pages?limit=3');

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(3, $data);

        // Check first page has most visits
        $this->assertEquals('https://example.com/page1', $data[0]['url']);
        $this->assertEquals(10, $data[0]['visits']);
    }

    /**
     * Test admin can get top content.
     */
    public function test_admin_can_get_top_content(): void
    {
        // Create some content
        $content1 = Content::factory()->create([
            'slug' => 'popular-article',
            'status' => 'published',
        ]);

        $content2 = Content::factory()->create([
            'slug' => 'less-popular',
            'status' => 'published',
        ]);

        // Create visits for content
        AnalyticsVisit::factory()->count(10)->create([
            'url' => 'https://example.com/content/popular-article',
            'visited_at' => now()->subDays(1),
        ]);

        AnalyticsVisit::factory()->count(3)->create([
            'url' => 'https://example.com/content/less-popular',
            'visited_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/top-content?limit=5');

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test admin can get device statistics.
     */
    public function test_admin_can_get_device_statistics(): void
    {
        // Create sessions from different devices
        \App\Models\AnalyticsSession::factory()->count(10)->create([
            'device_type' => 'desktop',
            'started_at' => now()->subDays(1),
        ]);

        \App\Models\AnalyticsSession::factory()->count(5)->create([
            'device_type' => 'mobile',
            'started_at' => now()->subDays(1),
        ]);

        \App\Models\AnalyticsSession::factory()->count(2)->create([
            'device_type' => 'tablet',
            'started_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/devices');

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test admin can get browser statistics.
     */
    public function test_admin_can_get_browser_statistics(): void
    {
        // Create sessions from different browsers
        \App\Models\AnalyticsSession::factory()->count(8)->create([
            'browser' => 'Chrome',
            'started_at' => now()->subDays(1),
        ]);

        \App\Models\AnalyticsSession::factory()->count(5)->create([
            'browser' => 'Firefox',
            'started_at' => now()->subDays(1),
        ]);

        \App\Models\AnalyticsSession::factory()->count(3)->create([
            'browser' => 'Safari',
            'started_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/browsers');

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test admin can get country statistics.
     */
    public function test_admin_can_get_country_statistics(): void
    {
        // Create sessions from different countries
        \App\Models\AnalyticsSession::factory()->count(10)->create([
            'country' => 'United States',
            'started_at' => now()->subDays(1),
        ]);

        \App\Models\AnalyticsSession::factory()->count(5)->create([
            'country' => 'United Kingdom',
            'started_at' => now()->subDays(1),
        ]);

        \App\Models\AnalyticsSession::factory()->count(3)->create([
            'country' => 'Indonesia',
            'started_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/countries');

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test admin can get referrer statistics.
     */
    public function test_admin_can_get_referrer_statistics(): void
    {
        // Create visits from different referrers
        AnalyticsVisit::factory()->count(10)->create([
            'referer' => 'https://google.com',
            'visited_at' => now()->subDays(1),
        ]);

        AnalyticsVisit::factory()->count(5)->create([
            'referer' => 'https://facebook.com',
            'visited_at' => now()->subDays(1),
        ]);

        AnalyticsVisit::factory()->count(3)->create([
            'referer' => 'https://twitter.com',
            'visited_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/referrers?limit=5');

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test admin can get event statistics.
     */
    public function test_admin_can_get_event_statistics(): void
    {
        // Create some analytics events
        AnalyticsEvent::factory()->count(10)->create([
            'event_type' => 'page_view',
            'event_name' => 'View Article',
            'created_at' => now()->subDays(1),
        ]);

        AnalyticsEvent::factory()->count(5)->create([
            'event_type' => 'click',
            'event_name' => 'Click Button',
            'created_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/events');

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test admin can get event stats grouped by type.
     */
    public function test_admin_can_get_event_stats(): void
    {
        // Create events of different types
        AnalyticsEvent::factory()->count(15)->create([
            'event_type' => 'page_view',
            'created_at' => now()->subDays(1),
        ]);

        AnalyticsEvent::factory()->count(8)->create([
            'event_type' => 'click',
            'created_at' => now()->subDays(1),
        ]);

        AnalyticsEvent::factory()->count(3)->create([
            'event_type' => 'form_submit',
            'created_at' => now()->subDays(1),
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/event-stats');

        TestHelpers::assertApiSuccess($response);

        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    /**
     * Test user without permission cannot access analytics.
     */
    public function test_user_without_permission_cannot_access_analytics(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/ja/analytics/overview');

        $response->assertStatus(403);
    }

    /**
     * Test unauthenticated user cannot access analytics.
     */
    public function test_unauthenticated_user_cannot_access_analytics(): void
    {
        $response = $this->getJson('/api/v1/admin/ja/analytics/overview');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/admin/ja/analytics/visits');
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/admin/ja/analytics/top-pages');
        $response->assertStatus(401);
    }
}
