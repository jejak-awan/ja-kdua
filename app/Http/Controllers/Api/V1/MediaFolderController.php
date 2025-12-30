<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\MediaFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MediaFolderController extends BaseApiController
{
    public function index(Request $request)
    {
        try {
            $query = MediaFolder::query();

            // Scope logic
            if ($request->user() && !$request->user()->can('manage media')) {
                $query->where('author_id', $request->user()->id);
            } elseif (!$request->user()) {
                 $query->whereNull('author_id');
            }

            // Handle trashed items
            if ($request->input('trashed') === 'only') {
                $query->onlyTrashed();
            } elseif ($request->input('trashed') === 'with') {
                $query->withTrashed();
            }

            if ($request->has('parent_id')) {
                if ($request->parent_id === 'null' || $request->parent_id === null) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            // Get tree structure if requested
            if ($request->has('tree') && $request->tree) {
                // Apply same scope to tree root finding
                // We need to apply the query constraint to the root query
                // Note: The original code used MediaFolder::whereNull('parent_id') direct call.
                // We must use the $query builder which has our scopes.
                $foldersQuery = $query->clone()->whereNull('parent_id');
                
                // Trashed handled in $query already? Yes, lines 18-21.
                // But creating a new query instance from 'MediaFolder::' might lose scope.
                // Let's rely on $query which already has scopes.
                // However $query might have other filters from request.
                // Let's reset parent_id filter if present in $query (it shouldn't be for tree root usually)
                
                $folders = $foldersQuery->with(['children' => function ($q) use ($request) {
                        if ($request->input('trashed') === 'only') {
                            $q->onlyTrashed();
                        } elseif ($request->input('trashed') === 'with') {
                            $q->withTrashed();
                        }
                        // We must also scope children?
                        // If parent is visible, children should be visible?
                        // If I own Parent, I own children.
                        // If Parent is Global, I might own a child.
                        // If Parent is Global, and Child is Other User's... I shouldn't see child.
                        if ($request->user() && !$request->user()->can('manage media')) {
                             $q->where('author_id', $request->user()->id);
                        }

                        $q->orderBy('sort_order')->with('children'); // Eager load sub-children
                    }])
                    ->orderBy('sort_order')
                    ->get();

                // Transform to include is_trashed and children_count for frontend
                $transformFolder = function($folder) use (&$transformFolder) {
                    $children = $folder->children ? $folder->children->map($transformFolder)->toArray() : [];
                    return [
                        'id' => $folder->id,
                        'name' => $folder->name,
                        'slug' => $folder->slug,
                        'parent_id' => $folder->parent_id,
                        'sort_order' => $folder->sort_order,
                        'is_trashed' => $folder->trashed(),
                        'children_count' => count($children),
                        'children' => $children,
                        'created_at' => $folder->created_at,
                        'updated_at' => $folder->updated_at,
                        'deleted_at' => $folder->deleted_at,
                        'author_id' => $folder->author_id, // Expose usage
                    ];
                };
                
                $foldersData = $folders->map($transformFolder);

                return $this->success($foldersData, 'Media folders tree retrieved successfully');
            }

            $folders = $query->with('parent')
                ->orderBy('sort_order')
                ->get();

            $foldersData = $folders->map(function ($folder) {
                return [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'slug' => $folder->slug,
                    'parent_id' => $folder->parent_id,
                    'sort_order' => $folder->sort_order,
                    'is_trashed' => $folder->trashed(),
                    'parent' => $folder->parent ? [
                        'id' => $folder->parent->id,
                        'name' => $folder->parent->name,
                        'slug' => $folder->parent->slug,
                    ] : null,
                    'created_at' => $folder->created_at,
                    'updated_at' => $folder->updated_at,
                    'deleted_at' => $folder->deleted_at,
                    'author_id' => $folder->author_id,
                ];
            });

            return $this->success($foldersData, 'Media folders retrieved successfully');
        } catch (\Exception $e) {
            Log::error('Media folders index error: '.$e->getMessage());
            return $this->success([], 'Media folders retrieved successfully');
        }
    }

    public function store(Request $request)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('create media')) {
             // Assuming 'create media' is enough to create folders?
             // Or 'view media' + 'upload'? 
             // Seeder gave 'manage media' to Admin. 'view media' to Author.
             // Author needs to upload. Controllers check 'manage media' for upload? 
             // MediaController checked 'manage media' for upload. I need to fix that too!
        }
        // Just checking 'create media' which implies upload rights?
        // Let's use 'edit media' or 'create media'.
        // Code originally checked 'manage media'.
        // Author has 'view media', 'create media', 'edit media', 'delete media'.
        if (!$request->user()->can('create media') && !$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to create media folders');
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:media_folders,id',
                'sort_order' => 'integer|min:0',
                'author_id' => 'nullable|exists:users,id',
            ]);

            $validated['slug'] = Str::slug($validated['name']);
            
            // Allow Admin to set author, else force own
            if ($request->user()->can('manage media')) {
                 // can set author_id
            } else {
                 $validated['author_id'] = $request->user()->id;
            }

            // Ensure slug is unique within the same parent
            $originalSlug = $validated['slug'];
            $counter = 1;
            $parentQuery = MediaFolder::where('parent_id', $validated['parent_id'] ?? null);
            while ($parentQuery->clone()->where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter++;
            }

            $folder = MediaFolder::create($validated);

            return $this->success($folder, 'Folder created successfully');
        } catch (\Exception $e) {
            Log::error('Media folder create error: '.$e->getMessage());
            return $this->error($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $mediaFolder = MediaFolder::withTrashed()->findOrFail($id);
        
        // Scope check
        if (request()->user() && !request()->user()->can('manage media')) {
             if ($mediaFolder->author_id && $mediaFolder->author_id !== request()->user()->id) {
                 return $this->forbidden('You do not have permission to view this folder');
             }
        }
        
        return $this->success($mediaFolder->load(['parent', 'children']), 'Folder retrieved successfully');
    }

    public function update(Request $request, MediaFolder $mediaFolder)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('edit media')) {
             // Allow 'edit media' permission
             return $this->forbidden('You do not have permission to update media folders');
        }
        
        // Ownership check
        if (!request()->user()->can('manage media')) {
             if ($mediaFolder->author_id && $mediaFolder->author_id !== request()->user()->id) {
                 return $this->forbidden('You do not have permission to update this folder');
             }
             if (is_null($mediaFolder->author_id)) {
                 // Cannot edit Global folders
                 return $this->forbidden('You cannot update global folders');
             }
             unset($request['author_id']);
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'parent_id' => 'nullable|exists:media_folders,id',
                'sort_order' => 'integer|min:0',
                'author_id' => 'nullable|exists:users,id',
            ]);

            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
                
                // Ensure slug is unique
                $originalSlug = $validated['slug'];
                $counter = 1;
                $parentId = $validated['parent_id'] ?? $mediaFolder->parent_id;
                $parentQuery = MediaFolder::where('parent_id', $parentId);
                while ($parentQuery->clone()->where('slug', $validated['slug'])->where('id', '!=', $mediaFolder->id)->exists()) {
                    $validated['slug'] = $originalSlug . '-' . $counter++;
                }
            }

            $mediaFolder->update($validated);

            return $this->success($mediaFolder, 'Folder updated successfully');
        } catch (\Exception $e) {
            Log::error('Media folder update error: '.$e->getMessage());
            return $this->error($e->getMessage(), 500);
        }
    }
    
    public function destroy(Request $request, MediaFolder $mediaFolder)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('delete media')) {
            return $this->forbidden('You do not have permission to delete media folders');
        }
        
         // Ownership check
        if (!request()->user()->can('manage media')) {
             if ($mediaFolder->author_id && $mediaFolder->author_id !== request()->user()->id) {
                 return $this->forbidden('You do not have permission to delete this folder');
             }
             if (is_null($mediaFolder->author_id)) {
                 return $this->forbidden('You cannot delete global folders');
             }
        }

        $permanent = $request->boolean('permanent', false);
        
        if ($permanent) {
            return $this->forceDelete($request, $mediaFolder->id); // Pass ID to forceDelete
        }

        // Soft delete - recursive behavior is handled by MediaFolderObserver
        $mediaFolder->delete();

        return $this->success(null, 'Folder moved to trash');
    }

    public function restore(Request $request, $id)
    {
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to restore media folders');
        }

        $folder = MediaFolder::onlyTrashed()->findOrFail($id);
        // Recursive restore is handled by MediaFolderObserver
        $folder->restore();

        return $this->success($folder->fresh(), 'Folder restored successfully');
    }

    public function forceDelete(Request $request, $id)
    {
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to permanently delete media folders');
        }

        $folder = MediaFolder::withTrashed()->findOrFail($id);
        
        // Safety: Prevent deleting folders that have non-trashed children/media if context is weird
        if ($folder->children()->count() > 0 || $folder->media()->count() > 0) {
            return $this->validationError([
                'folder' => ['Cannot permanently delete folder that still has active contents. Clear contents first.'],
            ], 'Cannot permanently delete folder with active contents');
        }

        $folder->forceDelete();

        return $this->success(null, 'Folder permanently deleted');
    }

    public function move(Request $request, MediaFolder $mediaFolder)
    {
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to move media folders');
        }

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
