<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ActivityLog;
use App\Models\Media;
use App\Models\User;
use App\Models\Tag;
use App\Services\CacheService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(name="Media")
 */
class MediaController extends BaseApiController
{
    protected MediaService $mediaService;

    public function __construct()
    {
        $this->mediaService = new MediaService;
    }

    public function index(Request $request)
    {
        $query = Media::with(['folder', 'usages', 'tags']);

        // Scope logic - apply BEFORE trash handling
        if ($request->user() && ! $request->user()->can('manage media')) {
            $query->where(function ($q) use ($request) {
                $q->where('author_id', $request->user()->id)
                    ->orWhere('is_shared', true);
            });
        } elseif (! $request->user()) {
            $query->where('is_shared', true);
        }

        // Handle trashed items (applied after scoping)
        if ($request->input('trashed') === 'only') {
            $query->onlyTrashed();
        } elseif ($request->input('trashed') === 'with') {
            $query->withTrashed();
        }

        if ($request->has('mime_type')) {
            $query->where('mime_type', 'like', $request->mime_type.'%');
        }

        if ($request->has('tag')) {
            $tag = $request->tag;
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('name', $tag)->orWhere('slug', $tag);
            });
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

        if ($request->has('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        if ($request->has('min_size')) {
            $query->where('size', '>=', $request->min_size);
        }

        if ($request->has('max_size')) {
            $query->where('size', '<=', $request->max_size);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->input('per_page', 24);
        $media = $query->latest()->paginate($perPage);

        return $this->paginated($media, 'Media retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/media/statistics",
     *     summary="Get media statistics",
     *     tags={"Media"},
     *     @OA\Response(
     *         response=200,
     *         description="Statistics retrieved successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function statistics(Request $request)
    {
        $query = Media::query();

        // Scope stats if not a manager
        if ($request->user() && ! $request->user()->can('manage media')) {
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
            'types' => $typeBreakdown,
        ], 'Media statistics retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/media/upload",
     *     summary="Upload a single media file",
     *     tags={"Media"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(property="file", type="string", format="binary"),
     *                 @OA\Property(property="folder_id", type="integer"),
     *                 @OA\Property(property="alt", type="string"),
     *                 @OA\Property(property="caption", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Media uploaded successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function upload(Request $request)
    {
        if (! $request->user()->can('upload media') && ! $request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to upload media');
        }

        try {
            $maxSize = \App\Models\Setting::get('max_upload_size', 10240);

            $request->validate([
                'file' => ['required', 'file', 'max:'.$maxSize],
                'folder_id' => 'nullable|exists:media_folders,id',
                'optimize' => 'boolean',
                'min_width' => 'nullable|integer|min:1',
                'min_height' => 'nullable|integer|min:1',
                'max_width' => 'nullable|integer|min:1',
                'max_height' => 'nullable|integer|min:1',
                'author_id' => 'nullable|exists:users,id',
                'is_shared' => 'sometimes|boolean',
                'caption' => 'nullable|string',
                'alt' => 'nullable|string',
                'tags' => 'nullable|array',
            ]);

            // Additional image dimension validation if requested
            if (str_starts_with($request->file('file')->getMimeType(), 'image/')) {
                $dimensions = [];
                if ($request->has('min_width')) {
                    $dimensions[] = 'min_width='.$request->min_width;
                }
                if ($request->has('min_height')) {
                    $dimensions[] = 'min_height='.$request->min_height;
                }
                if ($request->has('max_width')) {
                    $dimensions[] = 'max_width='.$request->max_width;
                }
                if ($request->has('max_height')) {
                    $dimensions[] = 'max_height='.$request->max_height;
                }

                if (! empty($dimensions)) {
                    $request->validate([
                        'file' => 'dimensions:'.implode(',', $dimensions),
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
            $request->user()->can('manage media') ? $request->boolean('is_shared') : false,
            [
                'caption' => $request->input('caption'),
                'alt' => $request->input('alt'),
                'tags' => $request->input('tags') ?? [],
            ]
        );

        ActivityLog::log('uploaded_media', $media, [], $request->user(), "Uploaded media: {$media->name}");

        return $this->success([
            'media' => $media->fresh()->load(['folder', 'usages']),
            'url' => $media->url,
        ], 'Media uploaded successfully', 201);
    }

    public function uploadMultiple(Request $request)
    {
        if (! $request->user()->can('upload media') && ! $request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to upload media');
        }

        try {
            $maxSize = \App\Models\Setting::get('max_upload_size', 10240);

            $request->validate([
                'files' => 'required|array',
                'files.*' => ['required', 'file', 'max:'.$maxSize],
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
                    $request->user()->can('manage media') ? $request->boolean('is_shared') : false,
                    [] // Bulk upload usually doesn't have custom metadata per file yet
                );
                $uploadedMedia[] = $media->load(['folder', 'usages']);
                ActivityLog::log('uploaded_media', $media, [], $request->user(), "Uploaded media: {$media->name} (batch)");
            } catch (\Exception $e) {
                \Log::error('Bulk upload failed for a file: '.$e->getMessage());
            }
        }

        return $this->success([
            'media' => $uploadedMedia,
            'count' => count($uploadedMedia),
        ], 'Media uploaded successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/media/{media}",
     *     summary="Get media details",
     *     tags={"Media"},
     *     @OA\Parameter(
     *         name="media",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media details retrieved"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function show(Media $media)
    {
        // Scope check
        if (request()->user() && ! request()->user()->can('manage media')) {
            if ($media->author_id !== request()->user()->id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to view this media');
            }
        }

        return $this->success($media->load(['folder', 'usages.model']), 'Media retrieved successfully');
    }

    public function usage(Media $media)
    {
        // Scope check
        if (request()->user() && ! request()->user()->can('manage media')) {
            if ($media->author_id !== request()->user()->id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to view this media');
            }
        }

        $usageInfo = $this->mediaService->getUsageInfo($media);

        return $this->success($usageInfo, 'Media usage retrieved successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/v1/media/{media}",
     *     summary="Update media metadata",
     *     tags={"Media"},
     *     @OA\Parameter(
     *         name="media",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="alt", type="string"),
     *             @OA\Property(property="caption", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media updated successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function update(Request $request, Media $media)
    {
        $user = $request->user();
        $isOwner = $media->author_id && $media->author_id === $user->id;
        $isManager = $user->can('manage media');

        // Owners can always update their own media
        if (! $isOwner && ! $isManager) {
            if (! $user->can('edit media')) {
                return $this->forbidden('You do not have permission to update media');
            }
            // Has edit media permission but still can't edit others' private media
            if ($media->author_id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to update this media');
            }
        }

        // Global media (no author) can only be updated by managers
        if (is_null($media->author_id) && ! $isManager) {
            return $this->forbidden('You cannot update global media');
        }

        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'alt' => 'nullable|string',
                'description' => 'nullable|string',
                'caption' => 'nullable|string',
                'author_id' => 'nullable|exists:users,id',
                'is_shared' => 'sometimes|boolean',
                'tags' => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        // Protection for sensitive fields
        if (! request()->user()->can('manage media')) {
            unset($validated['is_shared']);
            unset($validated['author_id']);
        }

        $media->update($validated);

        if ($request->has('tags')) {
            $this->mediaService->syncTags($media, $request->input('tags') ?? []);
        }

        ActivityLog::log('updated_media', $media, $validated, $request->user());

        $cacheService = new CacheService;
        $cacheService->clearMediaCaches();

        return $this->success($media->load(['folder', 'usages', 'tags']), 'Media updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/media/{media}",
     *     summary="Delete media",
     *     tags={"Media"},
     *     @OA\Parameter(
     *         name="media",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="permanent",
     *         in="query",
     *         description="Skip trash if true",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media deleted successfully"
     *     ),
     *     security={{"sanctum":{}}}
     * )
     */
    public function destroy(Request $request, Media $media)
    {
        \Illuminate\Support\Facades\Log::info('MediaController::destroy called', [
            'id' => $media->id,
            'path' => $media->path,
            'user' => $request->user()->id,
            'permanent' => $request->boolean('permanent')
        ]);
        
        $user = $request->user();
        $isOwner = $media->author_id && $media->author_id === $user->id;
        $isManager = $user->can('manage media');

        // Owners can always delete their own media
        if (! $isOwner && ! $isManager) {
            // Not owner and not manager - check if they have delete permission for shared items
            if (! $user->can('delete media')) {
                return $this->forbidden('You do not have permission to delete media');
            }
            // Has delete media permission but still can't delete others' private media
            if ($media->author_id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to delete this media');
            }
        }

        // Global media (no author) can only be deleted by managers
        if (is_null($media->author_id) && ! $isManager) {
            return $this->forbidden('You cannot delete global media');
        }

        $permanent = $request->boolean('permanent', false);

        if ($permanent) {
            return $this->forceDelete($request, $media->id); // Pass ID to forceDelete
        }

        // Check usage before soft delete - return warning but still proceed
        $usageCount = $media->usages()->count();

        $this->mediaService->delete($media, false);
        ActivityLog::log('soft_deleted_media', $media, [], $request->user());

        $response = [
            'message' => 'Media moved to trash',
            'usage_count' => $usageCount,
        ];

        if ($usageCount > 0) {
            $response['warning'] = "This media is used in {$usageCount} content(s). The references may break.";
        }

        return $this->success($response, 'Media moved to trash');
    }

    public function scan(Request $request)
    {
        if (! $request->user()->can('manage media')) {
            return $this->forbidden('You do not have permission to scan media');
        }

        $stats = $this->mediaService->scan();

        return $this->success($stats, 'Media scan completed successfully. Added ' . $stats['added'] . ' new files.');
    }

    public function restore(Request $request, $id)
    {
        \Illuminate\Support\Facades\Log::info('MediaController::restore called', [
            'id' => $id,
            'user' => $request->user()->id
        ]);

        $user = $request->user();
        $media = Media::onlyTrashed()->findOrFail($id);

        $isOwner = $media->author_id && $media->author_id === $user->id;
        $isManager = $user->can('manage media');

        // Owners can always restore their own media
        if (! $isOwner && ! $isManager) {
            if (! $user->can('delete media')) {
                return $this->forbidden('You do not have permission to restore media');
            }
            // Has delete media permission but still can't restore others' private media
            if ($media->author_id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to restore this media');
            }
        }

        // Global media (no author) can only be restored by managers
        if (is_null($media->author_id) && ! $isManager) {
            return $this->forbidden('You cannot restore global media');
        }

        $this->mediaService->restore($media->id);
        ActivityLog::log('restored_media', $media, [], $request->user());

        return $this->success($media->fresh()->load(['folder', 'usages']), 'Media restored successfully');
    }

    public function forceDelete(Request $request, $id)
    {
        \Illuminate\Support\Facades\Log::info('MediaController::forceDelete called', [
            'id' => $id,
            'user' => $request->user()->id,
            'force' => $request->boolean('force')
        ]);
        
        $user = $request->user();
        $media = Media::withTrashed()->findOrFail($id);

        $isOwner = $media->author_id && $media->author_id === $user->id;
        $isManager = $user->can('manage media');

        // Owners can always permanently delete their own media
        if (! $isOwner && ! $isManager) {
            if (! $user->can('delete media')) {
                return $this->forbidden('You do not have permission to permanently delete media');
            }
            // Has delete media permission but still can't delete others' private media
            if ($media->author_id && ! $media->is_shared) {
                return $this->forbidden('You do not have permission to manage this media');
            }
        }

        // Global media (no author) can only be deleted by managers
        if (is_null($media->author_id) && ! $isManager) {
            return $this->forbidden('You cannot delete global media');
        }

        // Safety check: if not forcing and in use, block
        $usageCount = $media->usages()->count();
        if ($usageCount > 0 && ! $request->input('force', false)) {
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
        if (! $request->user()->can('manage media') && ! $request->user()->can('delete media')) {
            return $this->forbidden('You do not have permission to empty trash');
        }

        // Get all trashed media (scoped)
        $query = Media::onlyTrashed();
        // Scope
        if (! $request->user()->can('manage media')) {
            $query->where('author_id', $request->user()->id);
        }
        $trashedMedia = $query->get();

        $deletedCount = 0;

        foreach ($trashedMedia as $media) {
            $this->mediaService->delete($media, true);
            ActivityLog::log('permanently_deleted_media', $media, [], $request->user(), "Permanently deleted media via empty trash: {$media->name}");
            $deletedCount++;
        }

        // Also delete trashed folders
        $folderQuery = \App\Models\MediaFolder::onlyTrashed();
        if (! $request->user()->can('manage media')) {
            $folderQuery->where('author_id', $request->user()->id);
        }
        $trashedFolders = $folderQuery->get();

        $deletedFoldersCount = 0;

        foreach ($trashedFolders as $folder) {
            $folder->forceDelete();
            ActivityLog::log('permanently_deleted_media_folder', $folder, [], $request->user(), "Permanently deleted media folder via empty trash: {$folder->name}");
            $deletedFoldersCount++;
        }

        return $this->success([
            'deleted_media' => $deletedCount,
            'deleted_folders' => $deletedFoldersCount,
        ], 'Trash emptied successfully');
    }

    public function bulkAction(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('MediaController::bulkAction called', [
            'action' => $request->input('action'),
            'ids' => $request->input('ids'),
            'user' => $request->user()->id
        ]);
        if (! $request->user()->can('manage media') && ! $request->user()->can('edit media') && ! $request->user()->can('delete media')) {
            return $this->forbidden('You do not have permission to perform bulk actions on media');
        }

        $ids = $request->input('ids', $request->input('media_ids', []));
        $action = $request->input('action');

        if ($action === 'move') {
            $action = 'move_folder';
        }

        $ids = $request->input('ids', []);
        $folderIds = $request->input('folder_ids', []);

        if (empty($ids) && empty($folderIds)) {
            return $this->validationError(['ids' => ['Media or Folder IDs are required.']], 'The selected action is invalid.');
        }

        // Filter Media IDs
        $existingMediaIds = [];
        if (!empty($ids)) {
            $query = Media::withTrashed()->whereIn('id', $ids);
            if (! $request->user()->can('manage media')) {
                $query->where('author_id', $request->user()->id);
            }
            $existingMediaIds = $query->pluck('id')->toArray();
        }

        // Filter Folder IDs
        $existingFolderIds = [];
        if (!empty($folderIds)) {
            $folderQuery = \App\Models\MediaFolder::withTrashed()->whereIn('id', $folderIds);
            if (! $request->user()->can('manage media')) {
                $folderQuery->where('author_id', $request->user()->id);
            }
            $existingFolderIds = $folderQuery->pluck('id')->toArray();
        }

        $folderId = $request->input('folder_id');
        if ($folderId === 'null') {
            $folderId = null;
        }

        $affected = $this->mediaService->bulkAction(
            $action,
            $existingMediaIds,
            $folderId,
            $request->input('alt_text'),
            $existingFolderIds
        );

        $totalAffected = $affected['media_count'] + $affected['folder_count'];
        ActivityLog::log('bulk_action_media', null, ['action' => $action, 'media_ids' => $existingMediaIds, 'folder_ids' => $existingFolderIds], $request->user(), "Performed bulk action '{$action}' on {$totalAffected} items");

        return $this->success($affected, 'Bulk action completed successfully');
    }

    public function generateThumbnail(Request $request, Media $media)
    {
        if (! $request->user()->can('manage media') && ! $request->user()->can('edit media')) {
            return $this->forbidden('You do not have permission to generate thumbnails');
        }

        // Ownership
        if (! $request->user()->can('manage media')) {
            if ($media->author_id && $media->author_id !== $request->user()->id) {
                return $this->forbidden('You do not have permission to modify this media');
            }
        }

        if (! str_starts_with($media->mime_type, 'image/')) {
            return $this->error('Thumbnail can only be generated for images', 400, [], 'INVALID_MEDIA_TYPE');
        }

        $width = $request->input('width', 300);
        $height = $request->input('height', 300);

        $thumbnailPath = $this->mediaService->generateThumbnail($media, $width, $height);

        if ($thumbnailPath) {
            $thumbnailUrl = $media->disk === 'public'
                ? '/storage/'.ltrim($thumbnailPath, '/')
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
        if (! $request->user()->can('manage media') && ! $request->user()->can('edit media')) {
            return $this->forbidden('You do not have permission to resize media');
        }

        // Ownership
        if (! $request->user()->can('manage media')) {
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

                    ActivityLog::log('resized_media', $media, ['width' => $width, 'height' => $height], $request->user());

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
        if (! $request->user()->can('manage media') && ! $request->user()->can('view media')) {
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
        if (! $request->user()->can('manage media')) {
            $query->where(function ($q) use ($request) {
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
        if (! $request->user()->can('manage media') && ! $request->user()->can('edit media')) {
            return $this->forbidden('You do not have permission to edit media');
        }

        // Ownership
        if (! $request->user()->can('manage media')) {
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
                            $newFileName = $customFilename.'.'.$pathInfo['extension'];
                        } else {
                            $newFileName = $pathInfo['filename'].'_edited_'.time().'.'.$pathInfo['extension'];
                        }

                        $newPath = $pathInfo['dirname'].'/'.$newFileName;

                        // Check if file exists, if so maybe error? Or append unique?
                        if ($customFilename && Storage::disk($media->disk)->exists($newPath)) {
                            $newFileName = $customFilename.'_'.time().'.'.$pathInfo['extension'];
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

                        ActivityLog::log('edited_media', $newMedia, [], $request->user(), "Created new version of media: {$newMedia->name}");

                        // Generate thumbnail if needed
                        if (config('media.generate_thumbnails', true)) {
                            try {
                                $thumbnailPath = $this->mediaService->generateThumbnail($newMedia);
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
                                $thumbnailPath = $this->mediaService->generateThumbnail($media);
                                ActivityLog::log('regenerated_thumbnail', $media, [], $request->user());
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

    /**
     * Get available filters for media
     */
    public function filters()
    {
        $tags = Tag::whereHas('media')->get(['id', 'name', 'slug']);
        
        $authors = User::whereHas('media')
            ->get(['id', 'name', 'avatar']);

        return $this->success([
            'tags' => $tags,
            'authors' => $authors,
        ]);
    }
}
