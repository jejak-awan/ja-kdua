<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    /**
     * Track a custom event
     *
     * @param  string  $eventType  Type of event (e.g., 'click', 'form_submit', 'download')
     * @param  string  $eventName  Name of the event (e.g., 'Button Click: Subscribe')
     * @param  array  $data  Additional event data
     * @param  int|null  $contentId  Related content ID (optional)
     * @return AnalyticsEvent
     */
    public static function trackEvent(string $eventType, string $eventName, array $data = [], ?int $contentId = null): AnalyticsEvent
    {
        try {
            $sessionId = session()->getId();
            
            // Ensure session exists
            if ($sessionId) {
                AnalyticsSession::start(request(), $sessionId);
            }

            return AnalyticsEvent::track($eventType, $eventName, $data, $contentId);
        } catch (\Exception $e) {
            Log::error('Analytics event tracking failed: '.$e->getMessage(), [
                'event_type' => $eventType,
                'event_name' => $eventName,
            ]);

            // Return a dummy event to prevent breaking the application
            return new AnalyticsEvent();
        }
    }

    /**
     * Track multiple events in batch
     *
     * @param  array  $events  Array of events [['type' => 'click', 'name' => 'Button', 'data' => []]]
     * @return array
     */
    public static function trackBatch(array $events): array
    {
        $tracked = [];
        foreach ($events as $event) {
            $tracked[] = self::trackEvent(
                $event['type'] ?? 'custom',
                $event['name'] ?? 'Unknown Event',
                $event['data'] ?? [],
                $event['content_id'] ?? null
            );
        }

        return $tracked;
    }

    /**
     * Track content interaction
     *
     * @param  int  $contentId  Content ID
     * @param  string  $action  Action type (view, like, share, comment)
     * @param  array  $data  Additional data
     * @return AnalyticsEvent
     */
    public static function trackContentInteraction(int $contentId, string $action, array $data = []): AnalyticsEvent
    {
        return self::trackEvent(
            'content_interaction',
            ucfirst($action).' Content',
            array_merge($data, ['content_id' => $contentId, 'action' => $action]),
            $contentId
        );
    }

    /**
     * Track form submission
     *
     * @param  string  $formName  Form name/identifier
     * @param  array  $data  Form data (sanitized)
     * @return AnalyticsEvent
     */
    public static function trackFormSubmission(string $formName, array $data = []): AnalyticsEvent
    {
        return self::trackEvent(
            'form_submit',
            'Form Submit: '.$formName,
            array_merge($data, ['form_name' => $formName])
        );
    }

    /**
     * Track download
     *
     * @param  string  $fileName  File name
     * @param  string  $fileType  File type/extension
     * @param  int|null  $mediaId  Media ID if applicable
     * @return AnalyticsEvent
     */
    public static function trackDownload(string $fileName, string $fileType, ?int $mediaId = null): AnalyticsEvent
    {
        return self::trackEvent(
            'download',
            'Download: '.$fileName,
            ['file_name' => $fileName, 'file_type' => $fileType, 'media_id' => $mediaId],
            $mediaId
        );
    }

    /**
     * Track search query
     *
     * @param  string  $query  Search query
     * @param  int  $resultsCount  Number of results
     * @return AnalyticsEvent
     */
    public static function trackSearch(string $query, int $resultsCount = 0): AnalyticsEvent
    {
        return self::trackEvent(
            'search',
            'Search: '.$query,
            ['query' => $query, 'results_count' => $resultsCount]
        );
    }

    /**
     * Track video play
     *
     * @param  string  $videoTitle  Video title
     * @param  int|null  $contentId  Content ID
     * @return AnalyticsEvent
     */
    public static function trackVideoPlay(string $videoTitle, ?int $contentId = null): AnalyticsEvent
    {
        return self::trackEvent(
            'video_play',
            'Video Play: '.$videoTitle,
            ['video_title' => $videoTitle],
            $contentId
        );
    }

    /**
     * Track button/link click
     *
     * @param  string  $buttonName  Button/link name
     * @param  string  $url  URL clicked
     * @return AnalyticsEvent
     */
    public static function trackClick(string $buttonName, string $url): AnalyticsEvent
    {
        return self::trackEvent(
            'click',
            'Click: '.$buttonName,
            ['button_name' => $buttonName, 'url' => $url]
        );
    }
}

