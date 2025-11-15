<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MediaFolder;
use Illuminate\Http\Request;

class MediaFolderController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaFolder::query();

        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Get tree structure if requested
        if ($request->has('tree') && $request->tree) {
            $folders = MediaFolder::whereNull('parent_id')
                ->with(['children' => function ($q) {
                    $q->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get();
            
            return response()->json($folders);
        }

        $folders = $query->with('parent')
            ->orderBy('sort_order')
            ->get();

        return response()->json($folders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:media_folders,slug',
            'parent_id' => 'nullable|exists:media_folders,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Auto-generate slug from name if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
            
            // Ensure slug is unique
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (MediaFolder::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Set default sort_order if not provided
        if (!isset($validated['sort_order'])) {
            $maxOrder = MediaFolder::where('parent_id', $validated['parent_id'] ?? null)
                ->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $request->input('id')) {
            return response()->json(['message' => 'Folder cannot be its own parent'], 422);
        }

        $folder = MediaFolder::create($validated);

        return response()->json($folder->load('parent'), 201);
    }

    public function show(MediaFolder $mediaFolder)
    {
        return response()->json($mediaFolder->load(['parent', 'children', 'media']));
    }

    public function update(Request $request, MediaFolder $mediaFolder)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|unique:media_folders,slug,' . $mediaFolder->id,
            'parent_id' => 'nullable|exists:media_folders,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $mediaFolder->id) {
            return response()->json(['message' => 'Folder cannot be its own parent'], 422);
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parent = MediaFolder::find($validated['parent_id']);
            if ($parent) {
                $checkParent = $parent;
                while ($checkParent->parent_id) {
                    if ($checkParent->parent_id == $mediaFolder->id) {
                        return response()->json(['message' => 'Cannot set parent to a child folder'], 422);
                    }
                    $checkParent = MediaFolder::find($checkParent->parent_id);
                }
            }
        }

        $mediaFolder->update($validated);

        return response()->json($mediaFolder->load(['parent', 'children']));
    }

    public function destroy(MediaFolder $mediaFolder)
    {
        // Check if has children
        if ($mediaFolder->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete folder with child folders',
                'children_count' => $mediaFolder->children()->count(),
            ], 422);
        }

        // Check if has media
        if ($mediaFolder->media()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete folder with media files',
                'media_count' => $mediaFolder->media()->count(),
            ], 422);
        }

        $mediaFolder->delete();

        return response()->json(['message' => 'Folder deleted successfully']);
    }

    public function move(Request $request, MediaFolder $mediaFolder)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:media_folders,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $mediaFolder->id) {
            return response()->json(['message' => 'Folder cannot be its own parent'], 422);
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parent = MediaFolder::find($validated['parent_id']);
            if ($parent) {
                $checkParent = $parent;
                while ($checkParent->parent_id) {
                    if ($checkParent->parent_id == $mediaFolder->id) {
                        return response()->json(['message' => 'Cannot set parent to a child folder'], 422);
                    }
                    $checkParent = MediaFolder::find($checkParent->parent_id);
                }
            }
        }

        $mediaFolder->update($validated);

        return response()->json($mediaFolder->load(['parent', 'children']));
    }
}
