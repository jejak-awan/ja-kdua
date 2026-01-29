<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TagController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = Tag::orderBy('name');

        // Admin/Manager can see all, others see own + global
        if ($request->user() && ! $request->user()->can('manage tags')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('author_id')->orWhere('author_id', $request->user()->id);
            });
        } elseif (! $request->user()) {
            // Public/Guest sees only global tags
            $query->whereNull('author_id');
        }

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('usage') && in_array($request->usage, ['used', 'unused', 'media'])) {
            if ($request->usage === 'used') {
                $query->has('contents');
            } elseif ($request->usage === 'media') {
                $query->has('media');
            } else {
                $query->doesntHave('contents')->doesntHave('media');
            }
        }

        if ($request->has('per_page')) {
            $perPage = (int) $request->get('per_page', 20);
            $tags = $query->withCount('contents')->paginate($perPage);

            return $this->success($tags, 'Tags retrieved successfully');
        }

        // Caching removed to support dynamic per-user scoping
        $tags = $query->get();

        return $this->success($tags, 'Tags retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:tags,slug',
            'description' => 'nullable|string',
            'author_id' => 'nullable|exists:users,id',
        ]);

        // Assign author logic
        if ($request->user()->can('manage tags')) {
            // Admin can assign author or leave null (Global)
        } else {
            // Regular user forces ownership
            $validated['author_id'] = $request->user()->id;
        }

        $tag = Tag::create($validated);

        $this->clearTagCaches();

        return $this->success($tag, 'Tag created successfully', 201);
    }

    public function show(Tag $tag)
    {
        // Scope check
        if (request()->user() && ! request()->user()->can('manage tags')) {
            if ($tag->author_id && $tag->author_id !== request()->user()->id) {
                return $this->forbidden('You do not have permission to view this tag');
            }
        }

        return $this->success($tag->load('contents'), 'Tag retrieved successfully');
    }

    public function update(Request $request, Tag $tag)
    {
        // Ownership check
        if (! request()->user()->can('manage tags')) {
            if ($tag->author_id && $tag->author_id !== request()->user()->id) {
                return $this->forbidden('You do not have permission to update this tag');
            }
            unset($request['author_id']);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:tags,slug,'.$tag->id,
            'description' => 'nullable|string',
            'author_id' => 'nullable|exists:users,id',
        ]);

        $tag->update($validated);

        $this->clearTagCaches();

        return $this->success($tag, 'Tag updated successfully');
    }

    public function destroy(Tag $tag)
    {
        // Ownership check
        if (! request()->user()->can('manage tags')) {
            if ($tag->author_id && $tag->author_id !== request()->user()->id) {
                return $this->forbidden('You do not have permission to delete this tag');
            }
            if (is_null($tag->author_id)) {
                return $this->forbidden('You do not have permission to delete global tags');
            }
        }

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

        $query = Tag::whereIn('id', $validated['ids']);

        // Scope deletion
        if (! $request->user()->can('manage tags')) {
            $query->where('author_id', $request->user()->id);
            // This implicitly prevents deleting Global tags (author_id is null)
        }

        $count = $query->delete();

        $this->clearTagCaches();

        return $this->success(['deleted_count' => $count], 'Tags deleted successfully');
    }

    public function statistics(Request $request)
    {
        $cacheKey = 'tags_statistics';
        if ($request->user() && ! $request->user()->can('manage settings')) {
            $cacheKey .= '_u'.$request->user()->id;
        }

        $stats = Cache::remember($cacheKey, now()->addHours(1), function () use ($request) {
            $query = Tag::query();

            // Scope if not manager
            if ($request->user() && ! $request->user()->can('manage settings')) {
                $query->where('author_id', $request->user()->id);
            }

            $tags = (clone $query)->withCount(['contents' => function ($q) use ($request) {
                // Also scope content count if author?
                // Currently tags are shared but have author_id?
                // Phase 10 says: "Add author_id to ... tags".
                // If tags are personal, then we only show personal tags.
                if ($request->user() && ! $request->user()->can('manage content')) {
                    $q->where('author_id', $request->user()->id);
                }
            }])->get();

            return [
                'total_tags' => $tags->count(),
                'used_tags' => $tags->filter(fn ($tag) => $tag->contents_count > 0)->count(),
                'unused_tags' => $tags->filter(fn ($tag) => $tag->contents_count === 0)->count(),
                'total_usage' => $tags->sum('contents_count'),
                'most_used' => $tags->sortByDesc('contents_count')->take(5)->map(fn ($tag) => [
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
