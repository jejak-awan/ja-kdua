<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\AnalyticsVisit;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        return response()->json($stats);
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

        return response()->json($visits);
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

        return response()->json($topPages);
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
                    ->where('url', 'like', '%' . $content->slug . '%')
                    ->count();
                $content->visits_count = $visits;
                return $content;
            })
            ->sortByDesc('visits_count')
            ->take($limit)
            ->values();

        return response()->json($topContent);
    }

    public function devices(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $devices = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->get();

        return response()->json($devices);
    }

    public function browsers(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $browsers = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->select('browser', DB::raw('count(*) as count'))
            ->groupBy('browser')
            ->orderByDesc('count')
            ->get();

        return response()->json($browsers);
    }

    public function countries(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $countries = AnalyticsVisit::whereBetween('visited_at', [$dateFrom, $dateTo])
            ->whereNotNull('country')
            ->select('country', DB::raw('count(*) as count'))
            ->groupBy('country')
            ->orderByDesc('count')
            ->get();

        return response()->json($countries);
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

        return response()->json($referrers);
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

        return response()->json($events);
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

        return response()->json($stats);
    }

    public function realTime()
    {
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

        return response()->json([
            'active_sessions' => $activeSessions,
            'visits_last_hour' => $visitsLastHour,
            'top_pages_now' => $topPagesNow,
        ]);
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
                return "DATE(visited_at) as period, count(*) as visits";
            case 'week':
                return "YEARWEEK(visited_at) as period, count(*) as visits";
            case 'month':
                return "DATE_FORMAT(visited_at, '%Y-%m') as period, count(*) as visits";
            default:
                return "DATE(visited_at) as period, count(*) as visits";
        }
    }

    protected function getGroupByField($groupBy)
    {
        switch ($groupBy) {
            case 'hour':
                return "DATE_FORMAT(visited_at, '%Y-%m-%d %H:00:00')";
            case 'day':
                return "DATE(visited_at)";
            case 'week':
                return "YEARWEEK(visited_at)";
            case 'month':
                return "DATE_FORMAT(visited_at, '%Y-%m')";
            default:
                return "DATE(visited_at)";
        }
    }
}
