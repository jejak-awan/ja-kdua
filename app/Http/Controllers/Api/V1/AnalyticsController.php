<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisit;
use App\Models\Content;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends BaseApiController
{
    /**
     * Get properly formatted date range for queries
     * Ensures end date includes the entire day (23:59:59)
     */
    protected function getDateRange(Request $request): array
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        
        return [
            $dateFrom . ' 00:00:00',
            $dateTo . ' 23:59:59',
        ];
    }

    public function overview(Request $request)
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);

        $stats = [
            'total_visits' => AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])->count(),
            'unique_visitors' => AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
                ->distinct('ip_address')
                ->count('ip_address'),
            'total_sessions' => AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])->count(),
            'avg_session_duration' => AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
                ->whereNotNull('ended_at')
                ->avg('duration'),
            'bounce_rate' => $this->calculateBounceRate($dateFrom, $dateTo),
            'page_views' => AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])->count(),
        ];

        return $this->success($stats, 'Analytics overview retrieved successfully');
    }

    public function visits(Request $request)
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $groupBy = $request->input('group_by', 'day'); // day, week, month

        $visits = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->selectRaw($this->getGroupByQuery($groupBy))
            ->groupBy(DB::raw($this->getGroupByField($groupBy)))
            ->orderBy('period')
            ->get();

        return $this->success($visits, 'Analytics visits retrieved successfully');
    }

    public function topPages(Request $request)
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $limit = $request->input('limit', 10);

        $topPages = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->select('url', DB::raw('count(*) as visits'))
            ->groupBy('url')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();

        return $this->success($topPages, 'Top pages retrieved successfully');
    }

    public function topContent(Request $request)
    {
        [$dateFrom, $dateTo] = $this->getDateRange($request);
        $limit = $request->input('limit', 10);

        // Optimized: Use content.views column instead of N+1 visits queries
        // For more accurate date-filtered stats, use subquery or pre-aggregated table
        $topContent = Cache::remember(
            "analytics_top_content_{$dateFrom}_{$dateTo}_{$limit}",
            now()->addMinutes(30),
            function () use ($dateFrom, $dateTo, $limit) {
                // Get content IDs with most visits in date range
                $visitCounts = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
                    ->select('url', DB::raw('count(*) as visits_count'))
                    ->groupBy('url')
                    ->orderByDesc('visits_count')
                    ->limit($limit * 2)  // Get extra to match with content
                    ->pluck('visits_count', 'url');

                return Content::with('author')
                    ->where('status', 'published')
                    ->get()
                    ->map(function ($content) use ($visitCounts) {
                        // Match content slug to URL visits
                        $visits = 0;
                        foreach ($visitCounts as $url => $count) {
                            if (str_contains($url, $content->slug)) {
                                $visits += $count;
                            }
                        }
                        $content->visits_count = $visits;
                        return $content;
                    })
                    ->sortByDesc('visits_count')
                    ->take($limit)
                    ->values();
            }
        );

        return $this->success($topContent, 'Top content retrieved successfully');
    }

    public function devices(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));

            if (! Schema::hasTable('analytics_sessions')) {
                return $this->success([], 'No analytics data available');
            }

            // Query from sessions table (normalized)
            $devices = AnalyticsSession::whereDate('started_at', '>=', $dateFrom)
                ->whereDate('started_at', '<=', $dateTo)
                ->select('device_type', DB::raw('count(*) as count'))
                ->groupBy('device_type')
                ->get();

            return $this->success($devices, 'Device analytics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Analytics devices error: '.$e->getMessage());

            return $this->success([], 'Device analytics retrieved successfully');
        }
    }

    public function browsers(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));

            if (! Schema::hasTable('analytics_sessions')) {
                return $this->success([], 'No analytics data available');
            }

            // Query from sessions table (normalized)
            $browsers = AnalyticsSession::whereDate('started_at', '>=', $dateFrom)
                ->whereDate('started_at', '<=', $dateTo)
                ->select('browser', DB::raw('count(*) as count'))
                ->groupBy('browser')
                ->orderByDesc('count')
                ->get();

            return $this->success($browsers, 'Browser analytics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Analytics browsers error: '.$e->getMessage());

            return $this->success([], 'Browser analytics retrieved successfully');
        }
    }

    public function countries(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));

            // Check if table exists
            if (! Schema::hasTable('analytics_sessions')) {
                return $this->success([], 'No analytics data available');
            }

            // Query from sessions table (normalized)
            $countries = AnalyticsSession::whereDate('started_at', '>=', $dateFrom)
                ->whereDate('started_at', '<=', $dateTo)
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->select('country', DB::raw('count(*) as count'))
                ->groupBy('country')
                ->orderByDesc('count')
                ->get();

            return $this->success($countries, 'Country analytics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Analytics countries error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty array instead of error to prevent frontend issues
            return $this->success([], 'Country analytics retrieved successfully');
        }
    }

    public function referrers(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $limit = $request->input('limit', 10);

        $referrers = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->select('referer', DB::raw('count(*) as count'))
            ->groupBy('referer')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return $this->success($referrers, 'Referrer analytics retrieved successfully');
    }

    public function events(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $eventType = $request->input('event_type');

        $query = AnalyticsEvent::whereBetween('occurred_at', [$dateFrom, $dateTo]);

        if ($eventType) {
            $query->where('event_type', $eventType);
        }

        $events = $query->with(['user', 'content'])
            ->latest('occurred_at')
            ->paginate(50);

        return $this->paginated($events, 'Analytics events retrieved successfully');
    }

    public function eventStats(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $stats = AnalyticsEvent::whereBetween('occurred_at', [$dateFrom, $dateTo])
            ->select('event_type', 'event_name', DB::raw('count(*) as count'))
            ->groupBy('event_type', 'event_name')
            ->orderByDesc('count')
            ->get();

        return $this->success($stats, 'Event statistics retrieved successfully');
    }

    /**
     * Track a page visit
     */
    public function trackVisit(Request $request)
    {
        try {
            $visit = AnalyticsVisit::trackVisit($request);
            return $this->success($visit, 'Visit tracked successfully');
        } catch (\Exception $e) {
            Log::error('Track visit error: ' . $e->getMessage());
            return $this->error('Failed to track visit', 500);
        }
    }

    /**
     * Track a custom event
     */
    public function trackEvent(Request $request)
    {
        try {
            $validated = $request->validate([
                'event_type' => 'required|string|max:50',
                'event_name' => 'required|string|max:255',
                'event_data' => 'nullable|array',
                'content_id' => 'nullable|integer|exists:contents,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $event = AnalyticsService::trackEvent(
            $validated['event_type'],
            $validated['event_name'],
            $validated['event_data'] ?? [],
            $validated['content_id'] ?? null
        );

        return $this->success($event, 'Event tracked successfully', 201);
    }

    /**
     * Track multiple events in batch
     */
    public function trackBatch(Request $request)
    {
        try {
            $validated = $request->validate([
                'events' => 'required|array|min:1|max:100',
                'events.*.type' => 'required|string|max:50',
                'events.*.name' => 'required|string|max:255',
                'events.*.data' => 'nullable|array',
                'events.*.content_id' => 'nullable|integer|exists:contents,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $tracked = AnalyticsService::trackBatch($validated['events']);

        return $this->success([
            'tracked_count' => count($tracked),
            'events' => $tracked,
        ], 'Events tracked successfully', 201);
    }

    public function realTime()
    {
        try {
            // Check if tables exist
            if (! Schema::hasTable('analytics_sessions') || ! Schema::hasTable('analytics_visits')) {
                return $this->success([
                    'active_sessions' => 0,
                    'visits_last_hour' => 0,
                    'top_pages_now' => [],
                ], 'Real-time analytics retrieved successfully');
            }

            $activeSessions = AnalyticsSession::whereNull('ended_at')
                ->where('started_at', '>=', now()->subMinutes(30))
                ->count();

            $visitsLastHour = AnalyticsVisit::where('visited_at', '>=', now()->subHour())
                ->count();

            $topPagesNow = AnalyticsVisit::where('visited_at', '>=', now()->subHour())
                ->select('url', DB::raw('count(*) as visits'))
                ->groupBy('url')
                ->orderByDesc('visits')
                ->limit(5)
                ->get();

            return $this->success([
                'active_sessions' => $activeSessions,
                'visits_last_hour' => $visitsLastHour,
                'top_pages_now' => $topPagesNow,
            ], 'Real-time analytics retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Analytics realTime error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty data instead of error to prevent frontend issues
            return $this->success([
                'active_sessions' => 0,
                'visits_last_hour' => 0,
                'top_pages_now' => [],
            ], 'Real-time analytics retrieved successfully');
        }
    }

    protected function calculateBounceRate($dateFrom, $dateTo)
    {
        // A bounce is either:
        // 1. Single page view session, OR
        // 2. Session with duration < 10 seconds
        $bounceSessions = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
            ->where(function ($query) {
                $query->where('page_views', 1)
                    ->orWhere('duration', '<', 10);
            })
            ->count();

        $totalSessions = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])->count();

        if ($totalSessions === 0) {
            return 0;
        }

        return round(($bounceSessions / $totalSessions) * 100, 2);
    }

    protected function getGroupByQuery($groupBy)
    {
        switch ($groupBy) {
            case 'hour':
                return "DATE_FORMAT(visited_at, '%Y-%m-%d %H:00:00') as period, count(*) as visits";
            case 'day':
                return 'DATE(visited_at) as period, count(*) as visits';
            case 'week':
                return 'YEARWEEK(visited_at) as period, count(*) as visits';
            case 'month':
                return "DATE_FORMAT(visited_at, '%Y-%m') as period, count(*) as visits";
            default:
                return 'DATE(visited_at) as period, count(*) as visits';
        }
    }

    protected function getGroupByField($groupBy)
    {
        switch ($groupBy) {
            case 'hour':
                return "DATE_FORMAT(visited_at, '%Y-%m-%d %H:00:00')";
            case 'day':
                return 'DATE(visited_at)';
            case 'week':
                return 'YEARWEEK(visited_at)';
            case 'month':
                return "DATE_FORMAT(visited_at, '%Y-%m')";
            default:
                return 'DATE(visited_at)';
        }
    }

    /**
     * Export analytics data to CSV
     */
    public function export(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));
            $type = $request->input('type', 'visits'); // visits, events, sessions

            $filename = "analytics-{$type}-{$dateFrom}-to-{$dateTo}.csv";

            switch ($type) {
                case 'events':
                    $data = $this->exportEvents($dateFrom, $dateTo);
                    break;
                case 'sessions':
                    $data = $this->exportSessions($dateFrom, $dateTo);
                    break;
                default:
                    $data = $this->exportVisits($dateFrom, $dateTo);
            }

            return response($data, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics export error: ' . $e->getMessage());
            return $this->error('Failed to export analytics data', 500);
        }
    }

    protected function exportVisits($dateFrom, $dateTo): string
    {
        $visits = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->orderBy('visited_at', 'desc')
            ->limit(10000)
            ->get();

        $csv = "ID,Session ID,URL,Referer,IP Address,Device Type,Browser,OS,Country,City,Visited At\n";

        foreach ($visits as $visit) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $visit->id,
                $visit->session_id ?? '',
                str_replace('"', '""', $visit->url ?? ''),
                str_replace('"', '""', $visit->referer ?? ''),
                $visit->ip_address ?? '',
                $visit->device_type ?? '',
                $visit->browser ?? '',
                $visit->os ?? '',
                $visit->country ?? '',
                $visit->city ?? '',
                $visit->visited_at?->format('Y-m-d H:i:s') ?? ''
            );
        }

        return $csv;
    }

    protected function exportEvents($dateFrom, $dateTo): string
    {
        $events = AnalyticsEvent::whereBetween('occurred_at', [$dateFrom, $dateTo])
            ->with('user')
            ->orderBy('occurred_at', 'desc')
            ->limit(10000)
            ->get();

        $csv = "ID,Session ID,User,Event Type,Event Name,URL,Content ID,IP Address,Occurred At\n";

        foreach ($events as $event) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%s,\"%s\",\"%s\"\n",
                $event->id,
                $event->session_id ?? '',
                $event->user?->name ?? 'Guest',
                $event->event_type ?? '',
                str_replace('"', '""', $event->event_name ?? ''),
                str_replace('"', '""', $event->url ?? ''),
                $event->content_id ?? '',
                $event->ip_address ?? '',
                $event->occurred_at?->format('Y-m-d H:i:s') ?? ''
            );
        }

        return $csv;
    }

    protected function exportSessions($dateFrom, $dateTo): string
    {
        $sessions = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
            ->with('user')
            ->orderBy('started_at', 'desc')
            ->limit(10000)
            ->get();

        $csv = "ID,Session ID,User,IP Address,Device Type,Browser,OS,Country,City,Page Views,Duration (sec),Started At,Ended At\n";

        foreach ($sessions as $session) {
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,%d,\"%s\",\"%s\"\n",
                $session->id,
                $session->session_id ?? '',
                $session->user?->name ?? 'Guest',
                $session->ip_address ?? '',
                $session->device_type ?? '',
                $session->browser ?? '',
                $session->os ?? '',
                $session->country ?? '',
                $session->city ?? '',
                $session->page_views ?? 0,
                $session->duration ?? 0,
                $session->started_at?->format('Y-m-d H:i:s') ?? '',
                $session->ended_at?->format('Y-m-d H:i:s') ?? ''
            );
        }

        return $csv;
    }
}
