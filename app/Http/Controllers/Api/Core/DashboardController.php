<?php

namespace App\Http\Controllers\Api\Core;

use App\Models\Core\AnalyticsVisit;
use App\Models\Core\Content;
use App\Models\Core\Media;
use App\Models\Core\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(name="Dashboard")
 */
class DashboardController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api/admin/ja/dashboard/admin",
     *     summary="Get admin dashboard data",
     *     tags={"Dashboard"},
     *
     *     @OA\Response(response=200, description="Dashboard data retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function admin(Request $request): \Illuminate\Http\JsonResponse
    {
        return Cache::remember('dashboard_admin_data', 300, function (): \Illuminate\Http\JsonResponse {
            return $this->success([
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
        });
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/dashboard/creator",
     *     summary="Get creator dashboard data",
     *     tags={"Dashboard"},
     *
     *     @OA\Parameter(name="days", in="query", @OA\Schema(type="integer", default=30)),
     *
     *     @OA\Response(response=200, description="Dashboard data retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function creator(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        /** @var \App\Models\Core\User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        $userId = $user->id;
        $daysRaw = $request->input('days', 30);
        $days = is_numeric($daysRaw) ? (int) $daysRaw : 30;

        $cacheKey = "dashboard_creator_data_{$userId}_{$days}";

        return Cache::remember($cacheKey, 300, function () use ($userId, $days): \Illuminate\Http\JsonResponse {
            return $this->success([
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
        });
    }

    /**
     * @OA\Get(
     *     path="/api/admin/ja/dashboard/viewer",
     *     summary="Get viewer dashboard data",
     *     tags={"Dashboard"},
     *
     *     @OA\Response(response=200, description="Dashboard data retrieved successfully"),
     *     security={{"sanctum":{}}}
     * )
     */
    public function viewer(Request $request): \Illuminate\Http\JsonResponse
    {
        return Cache::remember('dashboard_viewer_data', 600, function (): \Illuminate\Http\JsonResponse {
            return $this->success(Content::where('status', 'published')
                ->latest()
                ->take(5)
                ->select('id', 'title', 'created_at')
                ->get());
        });
    }

    // Helper methods
    /**
     * @return array{total: int, published: int, draft: int, pending: int}
     */
    private function getContentStats(): array
    {
        return [
            'total' => Content::count(),
            'published' => Content::where('status', 'published')->count(),
            'draft' => Content::where('status', 'draft')->count(),
            'pending' => Content::where('status', 'pending')->count(),
        ];
    }

    /**
     * @return array{total: int, images: int, videos: int, documents: int}
     */
    private function getMediaStats(): array
    {
        return [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'videos' => Media::where('mime_type', 'like', 'video/%')->count(),
            'documents' => Media::where('mime_type', 'not like', 'image/%')
                ->where('mime_type', 'not like', 'video/%')->count(),
        ];
    }

    /**
     * @return array{total: int, active: int}
     */
    private function getUserStats(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\Content>
     */
    private function getContentByStatus(): \Illuminate\Support\Collection
    {
        return Content::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\Media>
     */
    private function getMediaByType(): \Illuminate\Support\Collection
    {
        return Media::select(DB::raw("split_part(mime_type, '/', 1) as type"), DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\User>
     */
    private function getUserActivity(): \Illuminate\Support\Collection
    {
        return User::select(DB::raw('created_at::date as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * @return array{total: int, published: int, draft: int, pending: int}
     */
    private function getMyContentStats(int $userId): array
    {
        return [
            'total' => (int) Content::where('author_id', $userId)->count(),
            'published' => (int) Content::where('author_id', $userId)->where('status', 'published')->count(),
            'draft' => (int) Content::where('author_id', $userId)->where('status', 'draft')->count(),
            'pending' => (int) Content::where('author_id', $userId)->where('status', 'pending')->count(),
        ];
    }

    /**
     * @return array{total: int, size: float|int}
     */
    private function getMyMediaStats(int $userId): array
    {
        return [
            'total' => Media::where('author_id', $userId)->count(),
            'size' => (float) Media::where('author_id', $userId)->sum('size'),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\Content>
     */
    private function getMyContentByStatus(int $userId): \Illuminate\Support\Collection
    {
        return Content::where('author_id', $userId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\Content>
     */
    private function getMyTopContent(int $userId): \Illuminate\Support\Collection
    {
        return Content::where('author_id', $userId)
            ->orderBy('views', 'desc')
            ->take(5)
            ->select('id', 'title', 'slug', 'views', 'status', 'created_at', 'type')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Core\AnalyticsVisit>|array<empty, empty>
     */
    private function getMyContentTraffic(int $userId, int $days = 30): \Illuminate\Support\Collection|array
    {
        $slugs = Content::where('author_id', $userId)->pluck('slug')->toArray();

        if (empty($slugs)) {
            return [];
        }

        // Optimize: Use whereIn if URLs are simple slugs, or optimized LIKE if they are paths.
        // For CMS, URLs usually contain the slug at the end or as a segment.
        // We'll use a more efficient approach by limiting the strings we search for.
        $exactUrls = array_map(function ($s) {
            $sStr = is_scalar($s) ? (string) $s : '';

            return "/{$sStr}";
        }, $slugs);

        return AnalyticsVisit::where(function ($query) use ($exactUrls, $slugs) {
            $query->whereIn('url', $exactUrls);
            foreach ($slugs as $slug) {
                $slugStr = is_scalar($slug) ? (string) $slug : '';
                // Keep the LIKE for paths like /articles/slug-name
                $query->orWhere('url', 'like', "%/{$slugStr}");
            }
        })
            ->where('visited_at', '>=', now()->subDays($days))
            ->select(DB::raw('visited_at::date as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
