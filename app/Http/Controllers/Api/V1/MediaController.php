<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Media;
use App\Services\CacheService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends BaseApiController
{
    protected MediaService $mediaService;

    public function __construct()
    {
        $this->mediaService = new MediaService();
    }

    public function index(Request $request)
    {
        $query = Media::with(['folder', 'usages']);

        // Scope logic
        if ($request->user() && !$request->user()->can('manage media')) {
            $query->where(function ($q) use ($request) {
                $q->where('author_id', $request->user()->id)
                    ->orWhere('is_shared', true);
            });
        } elseif (!$request->user()) {
            $query->where('is_shared', true);
        }

        // Handle trashed items
        if ($request->input('trashed') === 'only') {
            $query->onlyTrashed();
        } elseif ($request->input('trashed') === 'with') {
            $query->withTrashed();
        }

        if ($request->has('mime_type')) {
            $query->where('mime_type', 'like', $request->mime_type.'%');
        }

        if ($request->has('folder_id')) {
            if ($request->folder_id === 'null' || $request->folder_id === null) {
                $query->whereNull('folder_id');
            } else {
                $query->where('folder_id', $request->folder_id);
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%");
            });
        }

        if ($request->has('usage')) {
            if ($request->usage === 'used') {
                $query->has('usages');
            } elseif ($request->usage === 'unused') {
                $query->doesntHave('usages');
            }
        }

        $perPage = $request->input('per_page', 24);
        $media = $query->latest()->paginate($perPage);

        return $this->paginated($media, 'Media retrieved successfully');
    }

    public function statistics(Request $request)
    {
        $query = Media::query();

        // Scope stats if not a manager
        if ($request->user() && !$request->user()->can('manage media')) {
            $query->where('author_id', $request->user()->id);
        }

        $totalMedia = (clone $query)->count();
        $totalSize = (clone $query)->sum('size');
        $trashCount = (clone $query)->onlyTrashed()->count();
        
        $typeBreakdown = (clone $query)->selectRaw('
            CASE 
                WHEN mime_type LIKE "image/%" THEN "image"
                WHEN mime_type LIKE "video/%" THEN "video"
                WHEN mime_type LIKE "application/%" THEN "document"
                ELSE "other"
            END as type,
            COUNT(*) as count
        ')
        ->groupBy('type')
        ->get();

        return $this->success([
            'total_count' => $totalMedia,
            'total_size' => $totalSize,
            'trash_count' => $trashCount,
            'types' => $typeBreakdown
        ], 'Media statistics retrieved successfully');
    }

    public function upload(Request $request)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('create media')) {
            return $this->forbidden('You do not have permission to upload media');
        }

        try {
            $maxSize = \App\Models\Setting::get('max_upload_size', 10240);
            
            $request->validate([
                'file' => ['required', 'file', 'max:' . $maxSize],
                'folder_id' => 'nullable|exists:media_folders,id',
                'optimize' => 'boolean',
                'min_width' => 'nullable|integer|min:1',
                'min_height' => 'nullable|integer|min:1',
                'max_width' => 'nullable|integer|min:1',
                'max_height' => 'nullable|integer|min:1',
                'author_id' => 'nullable|exists:users,id',
                'is_shared' => 'sometimes|boolean',
            ]);

            // Additional image dimension validation if requested
            if (str_starts_with($request->file('file')->getMimeType(), 'image/')) {
                $dimensions = [];
                if ($request->has('min_width')) $dimensions[] = 'min_width=' . $request->min_width;
                if ($request->has('min_height')) $dimensions[] = 'min_height=' . $request->min_height;
                if ($request->has('max_width')) $dimensions[] = 'max_width=' . $request->max_width;
                if ($request->has('max_height')) $dimensions[] = 'max_height=' . $request->max_height;

                if (!empty($dimensions)) {
                    $request->validate([
                        'file' => 'dimensions:' . implode(',', $dimensions),
                    ]);
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Determine author
        $authorId = $request->user()->id;
        if ($request->user()->can('manage media') && $request->has('author_id')) {
             $authorId = $request->input('author_id');
        }

        $media = $this->mediaService->upload(
            $request->file('file'),
            $request->input('folder_id'),
            $request->input('optimize', config('media.optimize', true)),
            $authorId,
            $request->user()->can('manage media') ? $request->boolean('is_shared') : false
        );

        return $this->success([
            'media' => $media->fresh()->load(['folder', 'usages']),
            'url' => $media->url,
        ], 'Media uploaded successfully', 201);
    }

    public function uploadMultiple(Request $request)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('create media')) {
            return $this->forbidden('You do not have permission to upload media');
        }

        try {
            $maxSize = \App\Models\Setting::get('max_upload_size', 10240);
            
            $request->validate([
                'files' => 'required|array',
                'files.*' => ['required', 'file', 'max:' . $maxSize],
                'folder_id' => 'nullable|exists:media_folders,id',
                'optimize' => 'boolean',
                'author_id' => 'nullable|exists:users,id',
                'is_shared' => 'sometimes|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $uploadedMedia = [];
        $files = $request->file('files');
        $folderId = $request->input('folder_id');
        $optimize = $request->input('optimize', config('media.optimize', true));
        
        // Determine author
        $authorId = $request->user()->id;
        if ($request->user()->can('manage media') && $request->has('author_id')) {
             $authorId = $request->input('author_id');
        }

        foreach ($files as $file) {
            try {
                $media = $this->mediaService->upload(
                    $file, 
                    $folderId, 
                    $optimize, 
                    $authorId,
                    $request->user()->can('manage media') ? $request->boolean('is_shared') : false
                );
                $uploadedMedia[] = $media->load(['folder', 'usages']);
            } catch (\Exception $e) {
                \Log::error('Bulk upload failed for a file: ' . $e->getMessage());
            }
        }

        return $this->success([
            'media' => $uploadedMedia,
            'count' => count($uploadedMedia),
        ], 'Media uploaded successfully', 201);
    }

    public function show(Media $media)
    {
        // Scope check
        if (request()->user() && !request()->user()->can('manage media')) {
             if ($media->author_id !== request()->user()->id && !$media->is_shared) {
                 return $this->forbidden('You do not have permission to view this media');
             }
        }
        return $this->success($media->load(['folder', 'usages.model']), 'Media retrieved successfully');
    }

    public function update(Request $request, Media $media)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('edit media')) {
            return $this->forbidden('You do not have permission to update media');
        }

        // Ownership check
        if (!request()->user()->can('manage media')) {
             if ($media->author_id && $media->author_id !== request()->user()->id) {
                 return $this->forbidden('You do not have permission to update this media');
             }
             if (is_null($media->author_id)) {
                 return $this->forbidden('You cannot update global media');
             }
             // Ensure author_id is not changed or unset
             // unset($request['author_id']); // Not in validated anyway
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'alt' => 'nullable|string',
                'description' => 'nullable|string',
                'author_id' => 'nullable|exists:users,id',
                'is_shared' => 'sometimes|boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Protection for is_shared
        if (isset($validated['is_shared']) && !request()->user()->can('manage media')) {
            unset($validated['is_shared']);
        }

        $media->update($validated);

        $cacheService = new CacheService();
        $cacheService->clearMediaCaches();

        return $this->success($media->load(['folder', 'usages']), 'Media updated successfully');
    }

    public function destroy(Request $request, Media $media)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('delete media')) {
            return $this->forbidden('You do not have permission to delete media');
        }

        // Ownership check
        if (!request()->user()->can('manage media')) {
             if ($media->author_id && $media->author_id !== request()->user()->id) {
                 return $this->forbidden('You do not have permission to delete this media');
             }
             if (is_null($media->author_id)) {
                 return $this->forbidden('You cannot delete global media');
             }
        }

        $permanent = $request->boolean('permanent', false);
        
        if ($permanent) {
            return $this->forceDelete($request, $media->id); // Pass ID to forceDelete
        }

        // Check usage before soft delete - return warning but still proceed
        $usageCount = $media->usages()->count();
        
        $this->mediaService->delete($media, false);

        $response = [
            'message' => 'Media moved to trash',
            'usage_count' => $usageCount,
        ];
        
        if ($usageCount > 0) {
            $response['warning'] = "This media is used in {$usageCount} content(s). The references may break.";
        }

        return $this->success($response, 'Media moved to trash');
    }

    public function restore(Request $request, $id)
    {
        if (!$request->user()->can('manage media')) { // Restore is usually advanced? Allow delete media users?
            // If I can delete, I should be able to restore?
            // Let's stick to 'manage media' or 'delete media' (implies trash management?)
            // Or maybe just 'manage media' for now.
             if (!$request->user()->can('delete media')) {
                  return $this->forbidden('You do not have permission to restore media');
             }
        }

        $media = Media::onlyTrashed()->findOrFail($id);
        
        // Ownership check
        if (!request()->user()->can('manage media')) {
             if ($media->author_id && $media->author_id !== request()->user()->id) {
                 return $this->forbidden('You do not have permission to restore this media');
             }
             if (is_null($media->author_id)) {
                 // Should allow restoring global if I am admin? Yes.
                 // If I am NOT admin, I cannot restore global.
                 return $this->forbidden('You cannot restore global media');
             }
        }
        
        $this->mediaService->restore($media->id);

        return $this->success($media->fresh()->load(['folder', 'usages']), 'Media restored successfully');
    }

    public function forceDelete(Request $request, $id)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('delete media')) {
            return $this->forbidden('You do not have permission to permanently delete media');
        }

        $media = Media::withTrashed()->findOrFail($id);
        
        // Ownership check
        if (!request()->user()->can('manage media')) {
             if ($media->author_id && $media->author_id !== request()->user()->id) {
                 return $this->forbidden('You do not have permission to manage this media');
             }
             if (is_null($media->author_id)) {
                 return $this->forbidden('You cannot delete global media');
             }
        }
        
        // Safety check: if not forcing and in use, block
        $usageCount = $media->usages()->count();
        if ($usageCount > 0 && !$request->input('force', false)) {
            return $this->validationError([
                'media' => ['Media is currently in use. Use force=true to delete anyway.'],
                'usage_count' => $usageCount,
            ], 'Media is currently in use.');
        }

        $this->mediaService->delete($media, true);

        return $this->success(null, 'Media permanently deleted');
    }

    /**
     * Empty all trash - permanently delete all trashed media and folders
     */
    public function emptyTrash(Request $request)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('delete media')) {
            return $this->forbidden('You do not have permission to empty trash');
        }

        // Get all trashed media (scoped)
        $query = Media::onlyTrashed();
         // Scope
        if (!$request->user()->can('manage media')) {
             $query->where('author_id', $request->user()->id);
        }
        $trashedMedia = $query->get();

        $deletedCount = 0;
        
        foreach ($trashedMedia as $media) {
            $this->mediaService->delete($media, true);
            $deletedCount++;
        }
        
        // Also delete trashed folders
        $folderQuery = \App\Models\MediaFolder::onlyTrashed();
        if (!$request->user()->can('manage media')) {
             $folderQuery->where('author_id', $request->user()->id);
        }
        $trashedFolders = $folderQuery->get();

        $deletedFoldersCount = 0;
        
        foreach ($trashedFolders as $folder) {
            $folder->forceDelete();
            $deletedFoldersCount++;
        }
        
        return $this->success([
            'deleted_media' => $deletedCount,
            'deleted_folders' => $deletedFoldersCount,
        ], 'Trash emptied successfully');
    }

    public function bulkAction(Request $request)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('edit media') && !$request->user()->can('delete media')) {
            return $this->forbidden('You do not have permission to perform bulk actions on media');
        }

        $ids = $request->input('ids', $request->input('media_ids', []));
        $action = $request->input('action');

        if ($action === 'move') {
            $action = 'move_folder';
        }

        if (empty($ids) || !is_array($ids)) {
            return $this->validationError(['ids' => ['Media IDs are required.']], 'The selected action is invalid.');
        }

        $query = Media::withTrashed()->whereIn('id', $ids);
        
        // Scope Logic
        if (!$request->user()->can('manage media')) {
            // Cannot touch Global items or other users' items
            // But if I want to "copy" or something? No, this is bulk action (delete, move)
            // Implicitly restrict to OWN items.
            $query->where('author_id', $request->user()->id);
        }

        $existingIds = $query->pluck('id')->toArray();
        if (count($existingIds) !== count($ids)) {
             // Some IDs were filtered out or didn't exist
             // return error or just proceed with valid ones?
             // UI might send mixed IDs. Let's just proceed with what we found but maybe warn?
             // The original code returned error if count mismatch.
             // If I am not manager, and I try to delete Admin's file, I should get error.
             return $this->validationError(['ids' => ['Some media IDs do not exist or you do not have permission.']], 'The selected action is invalid.');
        }

        $folderId = $request->input('folder_id');
        if ($folderId === 'null') {
            $folderId = null;
        }

        // If moving, check folder permission?
        if (($action === 'move' || $action === 'move_folder') && $folderId) {
             // Check if target folder is owned by me or global?
             // ...
        }

        $affected = $this->mediaService->bulkAction(
            $action,
            $existingIds, // Use filtered IDs
            $folderId,
            $request->input('alt_text')
        );

        return $this->success(['affected' => $affected], 'Bulk action completed successfully');
    }

    public function generateThumbnail(Request $request, Media $media)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('edit media')) {
            return $this->forbidden('You do not have permission to generate thumbnails');
        }
        
        // Ownership
        if (!$request->user()->can('manage media')) {
             if ($media->author_id && $media->author_id !== $request->user()->id) {
                 return $this->forbidden('You do not have permission to modify this media');
             }
        }

        if (!str_starts_with($media->mime_type, 'image/')) {
            return $this->error('Thumbnail can only be generated for images', 400, [], 'INVALID_MEDIA_TYPE');
        }

        $width = $request->input('width', 300);
        $height = $request->input('height', 300);

        $thumbnailPath = $this->mediaService->generateThumbnail($media, $width, $height);

        if ($thumbnailPath) {
            $thumbnailUrl = $media->disk === 'public'
                ? '/storage/' . ltrim($thumbnailPath, '/')
                : Storage::disk($media->disk)->url($thumbnailPath);

            return $this->success([
                'thumbnail_url' => $thumbnailUrl,
                'thumbnail_path' => $thumbnailPath,
                'media' => $media->fresh()->load(['folder', 'usages']),
            ], 'Thumbnail generated successfully');
        }

        return $this->error('Image processing library not available', 500, [], 'IMAGE_PROCESSING_UNAVAILABLE');
    }

    public function resize(Request $request, Media $media)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('edit media')) {
            return $this->forbidden('You do not have permission to resize media');
        }
        
        // Ownership
        if (!$request->user()->can('manage media')) {
             if ($media->author_id && $media->author_id !== $request->user()->id) {
                 return $this->forbidden('You do not have permission to modify this media');
             }
        }

        // Only for images
        if (! str_starts_with($media->mime_type, 'image/')) {
            return $this->validationError(['media' => ['Resize can only be performed on images']], 'Resize can only be performed on images');
        }

        try {
            $validated = $request->validate([
                'width' => 'required|integer|min:1|max:5000',
                'height' => 'nullable|integer|min:1|max:5000',
                'quality' => 'nullable|integer|min:1|max:100',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            $fullPath = Storage::disk($media->disk)->path($media->path);
            $width = $validated['width'];
            $height = $validated['height'] ?? null;
            $quality = $validated['quality'] ?? 85;

            // Use Intervention Image v3 API
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $driver = null;
                if (extension_loaded('gd')) {
                    $driver = new \Intervention\Image\Drivers\Gd\Driver;
                } elseif (extension_loaded('imagick')) {
                    $driver = new \Intervention\Image\Drivers\Imagick\Driver;
                }

                if ($driver) {
                    $manager = new \Intervention\Image\ImageManager($driver);
                    $image = $manager->read($fullPath);

                    // Resize image
                    if ($height) {
                        $image->resize($width, $height);
                    } else {
                        $image->scale(width: $width);
                    }

                    // Save resized image
                    $image->save($fullPath, quality: $quality);

                    // Update file size
                    $media->update([
                        'size' => filesize($fullPath),
                    ]);

                    return $this->success([
                        'media' => $media->fresh()->load(['folder', 'usages']),
                        'url' => $media->disk === 'public'
                            ? '/storage/'.ltrim($media->path, '/')
                            : Storage::disk($media->disk)->url($media->path),
                    ], 'Image resized successfully');
                }
            }

            return $this->error('Image processing library not available', 500, [], 'IMAGE_PROCESSING_UNAVAILABLE');
        } catch (\Exception $e) {
            \Log::error('Image resize failed: '.$e->getMessage());

            return $this->error('Failed to resize image: '.$e->getMessage(), 500, [], 'IMAGE_RESIZE_ERROR');
        }
    }

    public function downloadZip(Request $request)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('view media')) {
             // Anyone who can view can download?
            return $this->forbidden('You do not have permission to download media');
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:media,id',
        ]);

        $ids = $request->input('ids');
        $query = Media::whereIn('id', $ids);
        
        // Scope
        if (!$request->user()->can('manage media')) {
             $query->where(function($q) use ($request) {
                  $q->whereNull('author_id')->orWhere('author_id', $request->user()->id);
             });
        }
        
        $media = $query->get();

        if ($media->isEmpty()) {
            return $this->error('No media files found or permission denied', 404, [], 'NO_MEDIA_FOUND');
        }

        // Create temporary ZIP file
        $zipFileName = 'media-'.now()->format('Y-m-d-His').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return $this->error('Failed to create ZIP file', 500, [], 'ZIP_CREATION_FAILED');
        }

        foreach ($media as $item) {
            $filePath = Storage::disk($item->disk)->path($item->path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $item->file_name ?: $item->name);
            }
        }

        $zip->close();

        // Return file download
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function edit(Request $request, Media $media)
    {
        if (!$request->user()->can('manage media') && !$request->user()->can('edit media')) {
            return $this->forbidden('You do not have permission to edit media');
        }
        
        // Ownership
        if (!$request->user()->can('manage media')) {
             if ($media->author_id && $media->author_id !== $request->user()->id) {
                 return $this->forbidden('You do not have permission to edit this media');
             }
        }

        // Only for images
        if (! str_starts_with($media->mime_type, 'image/')) {
            return $this->validationError(['media' => ['Image editing can only be performed on images']], 'Image editing can only be performed on images');
        }

        try {
            $validated = $request->validate([
                'image' => 'required|image|max:10240', // 10MB max
                'save_as_new' => 'nullable|boolean',
                'custom_filename' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            $saveAsNew = $request->boolean('save_as_new', false);
            $customFilename = $request->input('custom_filename');
            $imageFile = $request->file('image');

            // Use Intervention Image v3 API
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $driver = null;
                if (extension_loaded('gd')) {
                    $driver = new \Intervention\Image\Drivers\Gd\Driver;
                } elseif (extension_loaded('imagick')) {
                    $driver = new \Intervention\Image\Drivers\Imagick\Driver;
                }

                if ($driver) {
                    $manager = new \Intervention\Image\ImageManager($driver);
                    $image = $manager->read($imageFile->getRealPath());

                    if ($saveAsNew) {
                        // Save as new version
                        $originalPath = $media->path;
                        $pathInfo = pathinfo($originalPath);
                        
                        if ($customFilename) {
                            // Sanitize filename to be safe
                            $customFilename = \Illuminate\Support\Str::slug(pathinfo($customFilename, PATHINFO_FILENAME));
                            $newFileName = $customFilename . '.' . $pathInfo['extension'];
                        } else {
                            $newFileName = $pathInfo['filename'].'_edited_'.time().'.'.$pathInfo['extension'];
                        }
                        
                        $newPath = $pathInfo['dirname'].'/'.$newFileName;

                        // Check if file exists, if so maybe error? Or append unique?
                        if ($customFilename && Storage::disk($media->disk)->exists($newPath)) {
                             $newFileName = $customFilename . '_' . time() . '.' . $pathInfo['extension'];
                             $newPath = $pathInfo['dirname'].'/'.$newFileName;
                        }

                        // Save to same disk
                        $fullPath = Storage::disk($media->disk)->path($newPath);
                        $directory = dirname($fullPath);
                        if (! is_dir($directory)) {
                            mkdir($directory, 0755, true);
                        }

                        $image->save($fullPath);

                        // Create new media record
                        $newMedia = Media::create([
                            'name' => pathinfo($newFileName, PATHINFO_FILENAME),
                            'file_name' => $newFileName,
                            'path' => $newPath,
                            'mime_type' => $media->mime_type,
                            'size' => filesize($fullPath),
                            'disk' => $media->disk,
                            'folder_id' => $media->folder_id,
                            'alt' => $media->alt,
                            'description' => $media->description,
                            'author_id' => \Auth::id(), // Corrected from user_id to author_id
                        ]);

                        // Generate thumbnail if needed
                        if (config('media.generate_thumbnails', true)) {
                            try {
                                $thumbnailPath = $this->generateThumbnailForMedia($newMedia);
                                $newMedia->update(['thumbnail_path' => $thumbnailPath]);
                            } catch (\Exception $e) {
                                \Log::warning('Thumbnail generation failed for edited image: '.$e->getMessage());
                            }
                        }

                        return $this->success($newMedia->load(['folder', 'usages']), 'Image saved as new version successfully');
                    } else {
                        // Overwrite existing
                        $fullPath = Storage::disk($media->disk)->path($media->path);
                        $image->save($fullPath);

                        // Update file size
                        $media->update([
                            'size' => filesize($fullPath),
                        ]);

                        // Regenerate thumbnail
                        if (config('media.generate_thumbnails', true) && $media->thumbnail_path) {
                            try {
                                $thumbnailPath = $this->generateThumbnailForMedia($media);
                                $media->update(['thumbnail_path' => $thumbnailPath]);
                            } catch (\Exception $e) {
                                \Log::warning('Thumbnail regeneration failed: '.$e->getMessage());
                            }
                        }

                        // Invalidate cache
                        app(CacheService::class)->invalidateByPattern("media:{$media->id}:*");

                        return $this->success($media->fresh()->load(['folder', 'usages']), 'Image updated successfully');
                    }
                }
            }

            return $this->error('Image processing library not available', 500, [], 'IMAGE_PROCESSING_UNAVAILABLE');
        } catch (\Exception $e) {
            \Log::error('Image editing failed: '.$e->getMessage());

            return $this->error('Failed to edit image: '.$e->getMessage(), 500, [], 'IMAGE_EDIT_ERROR');
        }
    }

}
