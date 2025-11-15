<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::orderBy('name')->get();

        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:tags,slug',
            'description' => 'nullable|string',
        ]);

        $tag = Tag::create($validated);

        return response()->json($tag, 201);
    }

    public function show(Tag $tag)
    {
        return response()->json($tag->load('contents'));
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:tags,slug,' . $tag->id,
            'description' => 'nullable|string',
        ]);

        $tag->update($validated);

        return response()->json($tag);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json(['message' => 'Tag deleted successfully']);
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

        return response()->json($stats);
    }
}
