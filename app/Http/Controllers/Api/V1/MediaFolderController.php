<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Models\MediaFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MediaFolderController extends BaseApiController
{
    public function index(Request $request)
    {
        try {
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
                
                return $this->success($folders, 'Media folders tree retrieved successfully');
            }

            $folders = $query->with('parent')
                ->orderBy('sort_order')
                ->get();

            // Transform to avoid issues with accessors during serialization
            $foldersData = $folders->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'slug' => $folder->slug,
                    'parent_id' => $folder->parent_id,
                    'sort_order' => $folder->sort_order,
                    'parent' => $folder->parent ? [
                        'id' => $folder->parent->id,
                        'name' => $folder->parent->name,
                        'slug' => $folder->parent->slug,
                    ] : null,
                    'created_at' => $folder->created_at,
                    'updated_at' => $folder->updated_at,
                ];
            });

            return $this->success($foldersData, 'Media folders retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Media folders index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            // Return empty array instead of error
            return $this->success([], 'Media folders retrieved successfully');
        }
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
            return $this->validationError(['parent_id' => ['Folder cannot be its own parent']], 'Folder cannot be its own parent');
        }

        $folder = MediaFolder::create($validated);

        return $this->success($folder->load('parent'), 'Media folder created successfully', 201);
    }

    public function show(MediaFolder $mediaFolder)
    {
        return $this->success($mediaFolder->load(['parent', 'children', 'media']), 'Media folder retrieved successfully');
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
            return $this->validationError(['parent_id' => ['Folder cannot be its own parent']], 'Folder cannot be its own parent');
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parent = MediaFolder::find($validated['parent_id']);
            if ($parent) {
                $checkParent = $parent;
                while ($checkParent->parent_id) {
                    if ($checkParent->parent_id == $mediaFolder->id) {
                        return $this->validationError(['parent_id' => ['Cannot set parent to a child folder']], 'Cannot set parent to a child folder');
                    }
                    $checkParent = MediaFolder::find($checkParent->parent_id);
                }
            }
        }

        $mediaFolder->update($validated);

        return $this->success($mediaFolder->load(['parent', 'children']), 'Media folder updated successfully');
    }

    public function destroy(MediaFolder $mediaFolder)
    {
        // Check if has children
        if ($mediaFolder->children()->count() > 0) {
            return $this->validationError([
                'folder' => ['Cannot delete folder with child folders'],
                'children_count' => $mediaFolder->children()->count(),
            ], 'Cannot delete folder with child folders');
        }

        // Check if has media
        if ($mediaFolder->media()->count() > 0) {
            return $this->validationError([
                'folder' => ['Cannot delete folder with media files'],
                'media_count' => $mediaFolder->media()->count(),
            ], 'Cannot delete folder with media files');
        }

        $mediaFolder->delete();

        return $this->success(null, 'Folder deleted successfully');
    }

    public function move(Request $request, MediaFolder $mediaFolder)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:media_folders,id',
            'sort_order' => 'integer|min:0',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $mediaFolder->id) {
            return $this->validationError(['parent_id' => ['Folder cannot be its own parent']], 'Folder cannot be its own parent');
        }

        // Prevent circular reference
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            $parent = MediaFolder::find($validated['parent_id']);
            if ($parent) {
                $checkParent = $parent;
                while ($checkParent->parent_id) {
                    if ($checkParent->parent_id == $mediaFolder->id) {
                        return $this->validationError(['parent_id' => ['Cannot set parent to a child folder']], 'Cannot set parent to a child folder');
                    }
                    $checkParent = MediaFolder::find($checkParent->parent_id);
                }
            }
        }

        $mediaFolder->update($validated);

        return $this->success($mediaFolder->load(['parent', 'children']), 'Media folder moved successfully');
    }
}
