<?php

namespace Tests\Unit\Services;

use App\Models\AnalyticsEvent;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test trackEvent creates an analytics event.
     */
    public function test_track_event_creates_analytics_event(): void
    {
        $event = AnalyticsService::trackEvent('click', 'Button Click', ['button_id' => 'subscribe']);

        $this->assertInstanceOf(AnalyticsEvent::class, $event);
        $this->assertEquals('click', $event->event_type);
        $this->assertEquals('Button Click', $event->event_name);
    }

    /**
     * Test trackEvent handles exceptions gracefully.
     */
    public function test_track_event_handles_exceptions_gracefully(): void
    {
        Log::shouldReceive('error')->once();

        // This should not throw, even if there's an error
        $event = AnalyticsService::trackEvent('test', 'Test Event');

        $this->assertInstanceOf(AnalyticsEvent::class, $event);
    }

    /**
     * Test trackBatch tracks multiple events.
     */
    public function test_track_batch_tracks_multiple_events(): void
    {
        $events = [
            ['type' => 'click', 'name' => 'Button 1', 'data' => []],
            ['type' => 'click', 'name' => 'Button 2', 'data' => []],
            ['type' => 'view', 'name' => 'Page View', 'data' => []],
        ];

        $tracked = AnalyticsService::trackBatch($events);

        $this->assertCount(3, $tracked);
        $this->assertInstanceOf(AnalyticsEvent::class, $tracked[0]);
    }

    /**
     * Test trackBatch handles empty array.
     */
    public function test_track_batch_handles_empty_array(): void
    {
        $tracked = AnalyticsService::trackBatch([]);

        $this->assertIsArray($tracked);
        $this->assertEmpty($tracked);
    }

    /**
     * Test trackBatch handles events with missing fields.
     */
    public function test_track_batch_handles_missing_fields(): void
    {
        $events = [
            ['type' => 'click'], // Missing name and data
            ['name' => 'Event'], // Missing type
        ];

        $tracked = AnalyticsService::trackBatch($events);

        $this->assertCount(2, $tracked);
        // Should use defaults for missing fields
        $this->assertInstanceOf(AnalyticsEvent::class, $tracked[0]);
    }
}

