<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisit;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends BaseApiController
{
    public function overview(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

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
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
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
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
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
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $limit = $request->input('limit', 10);

        // Get top content by matching URL patterns
        $topContent = Content::with('author')
            ->get()
            ->map(function ($content) use ($dateFrom, $dateTo) {
                $visits = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
                    ->where('url', 'like', '%'.$content->slug.'%')
                    ->count();
                $content->visits_count = $visits;

                return $content;
            })
            ->sortByDesc('visits_count')
            ->take($limit)
            ->values();

        return $this->success($topContent, 'Top content retrieved successfully');
    }

    public function devices(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->input('date_to', now()->format('Y-m-d'));

            if (! Schema::hasTable('analytics_visits')) {
                return $this->success([], 'No analytics data available');
            }

            $devices = AnalyticsVisit::whereDate('visited_at', '>=', $dateFrom)
                ->whereDate('visited_at', '<=', $dateTo)
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

            if (! Schema::hasTable('analytics_visits')) {
                return $this->success([], 'No analytics data available');
            }

            $browsers = AnalyticsVisit::whereDate('visited_at', '>=', $dateFrom)
                ->whereDate('visited_at', '<=', $dateTo)
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
            if (! Schema::hasTable('analytics_visits')) {
                return $this->success([], 'No analytics data available');
            }

            // Check if country column exists
            if (! Schema::hasColumn('analytics_visits', 'country')) {
                return $this->success([], 'Country data not available');
            }

            // Use whereDate for better compatibility with SQLite and MySQL
            $countries = AnalyticsVisit::whereDate('visited_at', '>=', $dateFrom)
                ->whereDate('visited_at', '<=', $dateTo)
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
        $singlePageSessions = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])
            ->where('page_views', 1)
            ->count();

        $totalSessions = AnalyticsSession::whereBetween('started_at', [$dateFrom, $dateTo])->count();

        if ($totalSessions === 0) {
            return 0;
        }

        return round(($singlePageSessions / $totalSessions) * 100, 2);
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
}
