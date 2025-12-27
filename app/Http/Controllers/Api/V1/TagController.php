<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tag;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TagController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Tag::orderBy('name');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('usage') && in_array($request->usage, ['used', 'unused'])) {
            if ($request->usage === 'used') {
                $query->has('contents');
            } else {
                $query->doesntHave('contents');
            }
        }

        if ($request->has('per_page')) {
            $perPage = (int) $request->get('per_page', 20);
            $tags = $query->withCount('contents')->paginate($perPage);
            return $this->success($tags, 'Tags retrieved successfully');
        }

        $tags = Cache::remember('tags_all', now()->addHours(6), function () use ($query) {
            return $query->get();
        });

        return $this->success($tags, 'Tags retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:tags,slug',
            'description' => 'nullable|string',
        ]);

        $tag = Tag::create($validated);

        $this->clearTagCaches();

        return $this->success($tag, 'Tag created successfully', 201);
    }

    public function show(Tag $tag)
    {
        return $this->success($tag->load('contents'), 'Tag retrieved successfully');
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:tags,slug,' . $tag->id,
            'description' => 'nullable|string',
        ]);

        $tag->update($validated);

        $this->clearTagCaches();

        return $this->success($tag, 'Tag updated successfully');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        $this->clearTagCaches();

        return $this->success(null, 'Tag deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tags,id',
        ]);

        Tag::whereIn('id', $validated['ids'])->delete();

        $this->clearTagCaches();

        return $this->success(null, 'Tags deleted successfully');
    }

    public function statistics()
    {
        $stats = Cache::remember('tags_statistics', now()->addHours(1), function () {
            $tags = Tag::withCount('contents')->get();

            return [
                'total_tags' => $tags->count(),
                'used_tags' => $tags->filter(fn($tag) => $tag->contents_count > 0)->count(),
                'unused_tags' => $tags->filter(fn($tag) => $tag->contents_count === 0)->count(),
                'total_usage' => $tags->sum('contents_count'),
                'most_used' => $tags->sortByDesc('contents_count')->take(5)->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'usage_count' => $tag->contents_count,
                ])->values(),
            ];
        });

        return $this->success($stats, 'Tag statistics retrieved successfully');
    }

    protected function clearTagCaches(): void
    {
        Cache::forget('tags_all');
        Cache::forget('tags_statistics');
    }
}
