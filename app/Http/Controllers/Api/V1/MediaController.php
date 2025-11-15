<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::with(['folder', 'usages']);

        if ($request->has('mime_type')) {
            $query->where('mime_type', 'like', $request->mime_type . '%');
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

        // Filter by usage (used/unused)
        if ($request->has('usage')) {
            if ($request->usage === 'used') {
                $query->has('usages');
            } elseif ($request->usage === 'unused') {
                $query->doesntHave('usages');
            }
        }

        // View type (affects pagination)
        $perPage = $request->input('view', 'grid') === 'list' ? 50 : 24;
        $media = $query->latest()->paginate($perPage);

        return response()->json($media);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'folder_id' => 'nullable|exists:media_folders,id',
            'optimize' => 'boolean',
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');
        $fullPath = Storage::disk('public')->path($path);

        // Optimize image if requested and it's an image
        if ($request->input('optimize', true) && str_starts_with($file->getMimeType(), 'image/')) {
            try {
                // Use Intervention Image v3 API
                if (class_exists(\Intervention\Image\ImageManager::class)) {
                    // Try GD driver first, fallback to Imagick if available
                    $driver = null;
                    if (extension_loaded('gd')) {
                        $driver = new \Intervention\Image\Drivers\Gd\Driver();
                    } elseif (extension_loaded('imagick')) {
                        $driver = new \Intervention\Image\Drivers\Imagick\Driver();
                    }
                    
                    if ($driver) {
                        $manager = new \Intervention\Image\ImageManager($driver);
                        $image = $manager->read($fullPath);
                        
                        // Resize if too large (max 1920px width)
                        if ($image->width() > 1920) {
                            $image->scale(width: 1920);
                        }
                        
                        // Save with quality optimization
                        $image->save($fullPath, quality: 85);
                    }
                }
            } catch (\Exception $e) {
                // If optimization fails, continue with original
                \Log::warning('Image optimization failed: ' . $e->getMessage());
            }
        }

        $fileSize = filesize($fullPath);

        $media = Media::create([
            'name' => $file->getClientOriginalName(),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'disk' => 'public',
            'path' => $path,
            'size' => $fileSize,
            'alt' => $request->input('alt'),
            'description' => $request->input('description'),
            'folder_id' => $request->input('folder_id'),
        ]);

        // Auto-generate thumbnail for images
        if (str_starts_with($media->mime_type, 'image/')) {
            try {
                $this->generateThumbnailForMedia($media);
            } catch (\Exception $e) {
                \Log::warning('Auto-thumbnail generation failed: ' . $e->getMessage());
            }
        }

        // Clear media cache
        $cacheService = new CacheService();
        $cacheService->clearMediaCaches();

        return response()->json([
            'media' => $media->fresh()->load(['folder', 'usages']),
            'url' => $media->url,
        ], 201);
    }

    public function show(Media $media)
    {
        return response()->json($media->load(['folder', 'usages.model']));
    }

    public function update(Request $request, Media $media)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'alt' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $media->update($validated);

        // Clear cache
        $cacheService = new CacheService();
        $cacheService->clearMediaCaches();

        return response()->json($media->load(['folder', 'usages']));
    }

    public function destroy(Media $media)
    {
        // Check if media is in use
        $usageCount = $media->usages()->count();
        
        if ($usageCount > 0 && !$request->input('force', false)) {
            return response()->json([
                'message' => 'Media is currently in use. Use force=true to delete anyway.',
                'usage_count' => $usageCount,
                'usages' => $media->usages()->with('model')->get(),
            ], 422);
        }

        // Delete all thumbnails/variants if exist
        $this->deleteMediaVariants($media);

        // Delete file from storage
        Storage::disk($media->disk)->delete($media->path);
        
        // Delete usage records
        $media->usages()->delete();
        
        $media->delete();

        // Clear cache
        $cacheService = new CacheService();
        $cacheService->clearMediaCaches();

        return response()->json(['message' => 'Media deleted successfully']);
    }

    protected function deleteMediaVariants(Media $media)
    {
        // Delete thumbnail if exists
        $fileName = pathinfo($media->path, PATHINFO_FILENAME);
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);
        $thumbnailPath = 'media/thumbnails/' . $fileName . '_thumb.' . $extension;
        
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
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        // Generate thumbnail filename
        $fileName = pathinfo($media->path, PATHINFO_FILENAME);
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);
        $thumbnailFileName = $fileName . '_thumb.' . $extension;
        $thumbnailPath = 'media/thumbnails/' . $thumbnailFileName;
        $thumbnailFullPath = Storage::disk($media->disk)->path($thumbnailPath);

        // Use Intervention Image v3 API
        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $driver = null;
            if (extension_loaded('gd')) {
                $driver = new \Intervention\Image\Drivers\Gd\Driver();
            } elseif (extension_loaded('imagick')) {
                $driver = new \Intervention\Image\Drivers\Imagick\Driver();
            }
            
            if ($driver) {
                $manager = new \Intervention\Image\ImageManager($driver);
                $image = $manager->read($fullPath);
                
                // Create thumbnail (crop to fit)
                $image->cover($width, $height);
                
                // Save thumbnail
                $image->save($thumbnailFullPath, quality: 85);
                
                return $thumbnailPath;
            }
        }
        
        return null;
    }

    public function bulkAction(Request $request)
    {
        // Accept both 'ids' and 'media_ids' for compatibility
        $ids = $request->input('ids', $request->input('media_ids', []));
        $action = $request->input('action');
        
        // Normalize action: 'move' -> 'move_folder'
        if ($action === 'move') {
            $action = 'move_folder';
            // Update request to use normalized action for validation
            $request->merge(['action' => $action]);
        }

        // Validate IDs separately first
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'message' => 'The selected action is invalid.',
                'errors' => ['ids' => ['Media IDs are required.']],
            ], 422);
        }

        // Validate that all IDs exist
        $existingIds = Media::whereIn('id', $ids)->pluck('id')->toArray();
        $missingIds = array_diff($ids, $existingIds);
        
        if (!empty($missingIds)) {
            return response()->json([
                'message' => 'The selected action is invalid.',
                'errors' => ['ids' => ['Some media IDs do not exist.']],
            ], 422);
        }

        // Now validate action and folder_id
        $folderId = $request->input('folder_id', null);
        
        // Convert string "null" to actual null
        if ($folderId === 'null') {
            $folderId = null;
        }
        
        // Validate action
        $validated = $request->validate([
            'action' => 'required|in:delete,move_folder',
        ]);
        
        // Validate folder_id only if it's not null
        if ($folderId !== null) {
            $request->validate([
                'folder_id' => 'exists:media_folders,id',
            ]);
        }
        
        $validated['folder_id'] = $folderId;

        $media = Media::whereIn('id', $ids)->get();

        foreach ($media as $item) {
            switch ($validated['action']) {
                case 'delete':
                    $this->deleteMediaVariants($item);
                    Storage::disk($item->disk)->delete($item->path);
                    $item->delete();
                    break;
                case 'move_folder':
                    $item->update(['folder_id' => $validated['folder_id']]);
                    break;
            }
        }

        return response()->json([
            'message' => 'Bulk action completed successfully',
            'affected' => $media->count(),
        ]);
    }

    public function usage(Media $media)
    {
        $usages = $media->usages()->get();
        
        $usageData = $usages->map(function ($usage) {
            $model = null;
            try {
                // Try to load the model
                $modelClass = $usage->model_type;
                if (class_exists($modelClass)) {
                    $model = $modelClass::find($usage->model_id);
                }
            } catch (\Exception $e) {
                // Model might be deleted or class doesn't exist
                \Log::warning('Failed to load model for usage: ' . $e->getMessage());
            }
            
            return [
                'id' => $usage->id,
                'model_type' => $usage->model_type,
                'model_id' => $usage->model_id,
                'field_name' => $usage->field_name,
                'model' => $model ? [
                    'id' => $model->id,
                    'title' => $model->title ?? $model->name ?? 'N/A',
                    'slug' => $model->slug ?? null,
                    'type' => class_basename($usage->model_type),
                ] : [
                    'id' => $usage->model_id,
                    'title' => 'Deleted or not found',
                    'slug' => null,
                    'type' => class_basename($usage->model_type),
                ],
                'created_at' => $usage->created_at,
            ];
        });

        return response()->json([
            'media_id' => $media->id,
            'total_usage' => $usages->count(),
            'usages' => $usageData,
        ]);
    }

    public function generateThumbnail(Request $request, Media $media)
    {
        // Only for images
        if (!str_starts_with($media->mime_type, 'image/')) {
            return response()->json([
                'message' => 'Thumbnail can only be generated for images',
            ], 422);
        }

        try {
            // Get thumbnail dimensions from request or use defaults
            $width = $request->input('width', 300);
            $height = $request->input('height', 300);

            $thumbnailPath = $this->generateThumbnailForMedia($media, $width, $height);
            
            if ($thumbnailPath) {
                // Use relative path for public disk to avoid localhost URL issues
                $thumbnailUrl = $media->disk === 'public' 
                    ? '/storage/' . ltrim($thumbnailPath, '/')
                    : Storage::disk($media->disk)->url($thumbnailPath);
                
                return response()->json([
                    'message' => 'Thumbnail generated successfully',
                    'thumbnail_url' => $thumbnailUrl,
                    'thumbnail_path' => $thumbnailPath,
                    'media' => $media->fresh()->load(['folder', 'usages']),
                ]);
            }
            
            return response()->json([
                'message' => 'Image processing library not available',
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Thumbnail generation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate thumbnail: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resize(Request $request, Media $media)
    {
        // Only for images
        if (!str_starts_with($media->mime_type, 'image/')) {
            return response()->json([
                'message' => 'Resize can only be performed on images',
            ], 422);
        }

        $validated = $request->validate([
            'width' => 'required|integer|min:1|max:5000',
            'height' => 'nullable|integer|min:1|max:5000',
            'quality' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $fullPath = Storage::disk($media->disk)->path($media->path);
            $width = $validated['width'];
            $height = $validated['height'] ?? null;
            $quality = $validated['quality'] ?? 85;

            // Use Intervention Image v3 API
            if (class_exists(\Intervention\Image\ImageManager::class)) {
                $driver = null;
                if (extension_loaded('gd')) {
                    $driver = new \Intervention\Image\Drivers\Gd\Driver();
                } elseif (extension_loaded('imagick')) {
                    $driver = new \Intervention\Image\Drivers\Imagick\Driver();
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
                    
                    return response()->json([
                        'message' => 'Image resized successfully',
                        'media' => $media->fresh()->load(['folder', 'usages']),
                        'url' => $media->disk === 'public' 
                            ? '/storage/' . ltrim($media->path, '/')
                            : Storage::disk($media->disk)->url($media->path),
                    ]);
                }
            }
            
            return response()->json([
                'message' => 'Image processing library not available',
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Image resize failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to resize image: ' . $e->getMessage(),
            ], 500);
        }
    }
}
