<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\Media;
use App\Models\User;
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

        return response()->json([
            'stats' => [
                'myContents' => $this->getMyContentStats($userId),
                'myMedia' => $this->getMyMediaStats($userId),
            ],
            'charts' => [
                'myContentByStatus' => $this->getMyContentByStatus($userId),
                'contentTraffic' => $this->getMyContentTraffic($userId),
            ],
        ]);
    }

    /**
     * Get viewer dashboard data (minimal)
     */
    public function viewer(Request $request)
    {
        return response()->json([
            'recentContent' => Content::where('status', 'published')
                ->latest()
                ->take(5)
                ->select('id', 'title', 'created_at')
                ->get(),
        ]);
    }

    // Helper methods
    private function getContentStats()
    {
        return [
            'total' => Content::count(),
            'published' => Content::where('status', 'published')->count(),
            'draft' => Content::where('status', 'draft')->count(),
            'pending' => Content::where('status', 'pending')->count(),
        ];
    }

    private function getMediaStats()
    {
        return [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'videos' => Media::where('mime_type', 'like', 'video/%')->count(),
            'documents' => Media::where('mime_type', 'not like', 'image/%')
                           ->where('mime_type', 'not like', 'video/%')->count(),
        ];
    }

    private function getUserStats()
    {
        return [
            'total' => User::count(),
            'active' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    private function getContentByStatus()
    {
        return Content::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    private function getMediaByType()
    {
        // Group by mime_type simplification (image, video, etc)
        // Using substring to get valid grouping (MySQL/MariaDB compatible)
        return Media::select(DB::raw("SUBSTRING_INDEX(mime_type, '/', 1) as type"), DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();
    }

    private function getUserActivity()
    {
        return User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getMyContentStats($userId)
    {
        return [
            'total' => Content::where('author_id', $userId)->count(),
            'published' => Content::where('author_id', $userId)->where('status', 'published')->count(),
            'draft' => Content::where('author_id', $userId)->where('status', 'draft')->count(),
            'pending' => Content::where('author_id', $userId)->where('status', 'pending')->count(),
        ];
    }

    private function getMyMediaStats($userId)
    {
        return [
            'total' => Media::where('author_id', $userId)->count(),
            'size' => Media::where('author_id', $userId)->sum('size'),
        ];
    }

    private function getMyContentByStatus($userId)
    {
        return Content::where('author_id', $userId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    private function getMyContentTraffic($userId)
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
            ->where('visited_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(visited_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
