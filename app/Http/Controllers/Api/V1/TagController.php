<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends BaseApiController
{
    public function index()
    {
        $tags = Tag::orderBy('name')->get();

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

        return $this->success($tag, 'Tag updated successfully');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return $this->success(null, 'Tag deleted successfully');
    }

    public function statistics()
    {
        $tags = Tag::withCount('contents')->get();
        
        $stats = [
            'total_tags' => $tags->count(),
            'used_tags' => $tags->filter(function($tag) {
                return $tag->contents_count > 0;
            })->count(),
            'unused_tags' => $tags->filter(function($tag) {
                return $tag->contents_count === 0;
            })->count(),
            'total_usage' => $tags->sum('contents_count'),
            'most_used' => $tags->sortByDesc('contents_count')->take(5)->map(function($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'usage_count' => $tag->contents_count,
                ];
            })->values(),
        ];

        return $this->success($stats, 'Tag statistics retrieved successfully');
    }
}
