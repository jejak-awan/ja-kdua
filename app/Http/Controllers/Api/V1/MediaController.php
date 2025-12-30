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

    public function statistics()
    {
        $totalMedia = Media::count();
        $totalSize = Media::sum('size');
        $trashCount = Media::onlyTrashed()->count();
        
        $typeBreakdown = Media::selectRaw('
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
        if (!$request->user()->can('manage media')) {
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

        $media = $this->mediaService->upload(
            $request->file('file'),
            $request->input('folder_id'),
            $request->input('optimize', config('media.optimize', true))
        );

        return $this->success([
            'media' => $media->fresh()->load(['folder', 'usages']),
            'url' => $media->url,
        ], 'Media uploaded successfully', 201);
    }

    public function uploadMultiple(Request $request)
    {
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to upload media');
        }

        try {
            $maxSize = \App\Models\Setting::get('max_upload_size', 10240);
            
            $request->validate([
                'files' => 'required|array',
                'files.*' => ['required', 'file', 'max:' . $maxSize],
                'folder_id' => 'nullable|exists:media_folders,id',
                'optimize' => 'boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $uploadedMedia = [];
        $files = $request->file('files');
        $folderId = $request->input('folder_id');
        $optimize = $request->input('optimize', config('media.optimize', true));

        foreach ($files as $file) {
            try {
                $media = $this->mediaService->upload($file, $folderId, $optimize);
                $uploadedMedia[] = $media->load(['folder', 'usages']);
            } catch (\Exception $e) {
                Log::error('Bulk upload failed for a file: ' . $e->getMessage());
            }
        }

        return $this->success([
            'media' => $uploadedMedia,
            'count' => count($uploadedMedia),
        ], 'Media uploaded successfully', 201);
    }

    public function show(Media $media)
    {
        return $this->success($media->load(['folder', 'usages.model']), 'Media retrieved successfully');
    }

    public function update(Request $request, Media $media)
    {
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to update media');
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'alt' => 'nullable|string',
                'description' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $media->update($validated);

        $cacheService = new CacheService();
        $cacheService->clearMediaCaches();

        return $this->success($media->load(['folder', 'usages']), 'Media updated successfully');
    }

    public function destroy(Request $request, Media $media)
    {
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to delete media');
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
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to restore media');
        }

        $media = Media::onlyTrashed()->findOrFail($id);
        $this->mediaService->restore($media->id);

        return $this->success($media->fresh()->load(['folder', 'usages']), 'Media restored successfully');
    }

    public function forceDelete(Request $request, $id)
    {
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to permanently delete media');
        }

        $media = Media::withTrashed()->findOrFail($id);
        
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
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to empty trash');
        }

        // Get all trashed media
        $trashedMedia = Media::onlyTrashed()->get();
        $deletedCount = 0;
        
        foreach ($trashedMedia as $media) {
            $this->mediaService->delete($media, true);
            $deletedCount++;
        }
        
        // Also delete trashed folders
        $trashedFolders = \App\Models\MediaFolder::onlyTrashed()->get();
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

    protected function deleteMediaVariants(Media $media)
    {
        // Delete thumbnail if exists
        $fileName = pathinfo($media->path, PATHINFO_FILENAME);
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);
        $thumbnailPath = 'media/thumbnails/'.$fileName.'_thumb.'.$extension;

        if (Storage::disk($media->disk)->exists($thumbnailPath)) {
            Storage::disk($media->disk)->delete($thumbnailPath);
        }

        // Delete other sizes if exist
        $sizes = ['small', 'medium', 'large'];
        foreach ($sizes as $size) {
            $sizePath = str_replace('/media/', "/media/{$size}/", $media->path);
            if (Storage::disk($media->disk)->exists($sizePath)) {
                Storage::disk($media->disk)->delete($sizePath);
            }
        }
    }

    protected function generateThumbnailForMedia(Media $media, $width = 300, $height = 300)
    {
        $fullPath = Storage::disk($media->disk)->path($media->path);

        // Create thumbnails directory in storage
        $thumbnailDir = Storage::disk($media->disk)->path('media/thumbnails');
        if (! is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        // Generate thumbnail filename
        $fileName = pathinfo($media->path, PATHINFO_FILENAME);
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);

        // For SVG files, convert to PNG for thumbnail
        $isSvg = $media->mime_type === 'image/svg+xml' || strtolower($extension) === 'svg';
        $thumbnailExtension = $isSvg ? 'png' : $extension;
        $thumbnailFileName = $fileName.'_thumb.'.$thumbnailExtension;
        $thumbnailPath = 'media/thumbnails/'.$thumbnailFileName;
        $thumbnailFullPath = Storage::disk($media->disk)->path($thumbnailPath);

        // Handle SVG files - convert to PNG using Imagick (if available)
        if ($isSvg && extension_loaded('imagick') && class_exists('Imagick')) {
            try {
                $imagick = new \Imagick;
                $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
                $imagick->readImage($fullPath);
                $imagick->setImageFormat('png');

                // Resize SVG to thumbnail size
                $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);

                // Save as PNG
                $imagick->writeImage($thumbnailFullPath);
                $imagick->clear();
                $imagick->destroy();

                return $thumbnailPath;
            } catch (\Exception $e) {
                \Log::warning('SVG thumbnail generation failed with Imagick: '.$e->getMessage());
                // Fall through to try Intervention Image
            }
        }

        // Use Intervention Image v3 API for raster images
        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $driver = null;
            if (extension_loaded('gd')) {
                $driver = new \Intervention\Image\Drivers\Gd\Driver;
            } elseif (extension_loaded('imagick')) {
                $driver = new \Intervention\Image\Drivers\Imagick\Driver;
            }

            if ($driver) {
                try {
                    $manager = new \Intervention\Image\ImageManager($driver);
                    $image = $manager->read($fullPath);

                    // Create thumbnail (crop to fit)
                    $image->cover($width, $height);

                    // For SVG converted to PNG, save as PNG
                    if ($isSvg) {
                        $image->toPng()->save($thumbnailFullPath);
                    } else {
                        // Save thumbnail with original format
                        $image->save($thumbnailFullPath, quality: 85);
                    }

                    return $thumbnailPath;
                } catch (\Exception $e) {
                    \Log::warning('Thumbnail generation failed with Intervention Image: '.$e->getMessage());
                    // If SVG and both methods failed, return null (will use original)
                    if ($isSvg) {
                        return null;
                    }
                }
            }
        }

        return null;
    }

    public function bulkAction(Request $request)
    {
        if (!$request->user()->can('manage media')) {
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

        $existingIds = Media::withTrashed()->whereIn('id', $ids)->pluck('id')->toArray();
        if (count($existingIds) !== count($ids)) {
            return $this->validationError(['ids' => ['Some media IDs do not exist.']], 'The selected action is invalid.');
        }

        $folderId = $request->input('folder_id');
        if ($folderId === 'null') {
            $folderId = null;
        }

        $affected = $this->mediaService->bulkAction(
            $action,
            $ids,
            $folderId,
            $request->input('alt_text')
        );

        return $this->success(['affected' => $affected], 'Bulk action completed successfully');
    }

    public function usage(Media $media)
    {
        $usageInfo = $this->mediaService->getUsageInfo($media);

        return $this->success([
            'media_id' => $media->id,
            'usage_count' => count($usageInfo),
            'usages' => $usageInfo,
        ], 'Media usage retrieved successfully');
    }

    public function generateThumbnail(Request $request, Media $media)
    {
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to generate thumbnails');
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
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to resize media');
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

    /**
     * Download multiple media files as ZIP
     */
    public function downloadZip(Request $request)
    {
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to download media');
        }

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:media,id',
        ]);

        $ids = $request->input('ids');
        $media = Media::whereIn('id', $ids)->get();

        if ($media->isEmpty()) {
            return $this->error('No media files found', 404, [], 'NO_MEDIA_FOUND');
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
        if (!$request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to edit media');
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
                        // Let's append unique if custom name exists to avoid silent overwrite of unrelated file
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
                            'user_id' => auth()->id(),
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
