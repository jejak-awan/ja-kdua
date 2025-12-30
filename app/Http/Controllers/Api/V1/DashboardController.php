<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\Media;
use App\Models\User;
use App\Models\AnalyticsVisit;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard data (full access)
     */
    public function admin(Request $request)
    {
        return response()->json([
            'stats' => [
                'contents' => $this->getContentStats(),
                'media' => $this->getMediaStats(),
                'users' => $this->getUserStats(),
            ],
            'charts' => [
                'contentByStatus' => $this->getContentByStatus(),
                'mediaByType' => $this->getMediaByType(),
                'userActivity' => $this->getUserActivity(),
            ],
        ]);
    }

    /**
     * Get creator dashboard data (scoped to user)
     */
    public function creator(Request $request)
    {
        $userId = $request->user()->id;
        $days = $request->input('days', 30);

        return response()->json([
            'stats' => [
                'myContents' => $this->getMyContentStats($userId),
                'myMedia' => $this->getMyMediaStats($userId),
            ],
            'charts' => [
                'myContentByStatus' => $this->getMyContentByStatus($userId),
                'contentTraffic' => $this->getMyContentTraffic($userId, $days),
            ],
            'topContent' => $this->getMyTopContent($userId),
        ]);
    }
    
    // ... existing ...

    private function getMyTopContent($userId)
    {
        return Content::where('author_id', $userId)
            ->orderBy('views', 'desc')
            ->take(5)
            ->select('id', 'title', 'slug', 'views', 'status', 'created_at', 'type')
            ->get();
    }

    private function getMyContentTraffic($userId, $days = 30)
    {
        $slugs = Content::where('author_id', $userId)->pluck('slug');
        
        if ($slugs->isEmpty()) {
            return [];
        }

        return AnalyticsVisit::where(function ($query) use ($slugs) {
                foreach ($slugs as $slug) {
                    $query->orWhere('url', 'like', '%' . $slug);
                }
            })
            ->where('visited_at', '>=', now()->subDays($days))
            ->select(DB::raw('DATE(visited_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
