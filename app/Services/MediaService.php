<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    protected CacheService $cacheService;

    public function __construct()
    {
        $this->cacheService = new CacheService;
    }

    /**
     * Upload and process a media file
     */
    public function upload(UploadedFile $file, ?int $folderId = null, bool $optimize = true, ?int $authorId = null, bool $isShared = false): Media
    {
        // Check for SVG and sanitize BEFORE optimization or other processing
        if ($file->getMimeType() === 'image/svg+xml' || strtolower($file->getClientOriginalExtension()) === 'svg') {
            $this->sanitizeSvg($file->getRealPath());
        }

        $path = $file->store('media', 'public');
        $fullPath = Storage::disk('public')->path($path);

        // Optimize image if requested (and not SVG, since we just sanitized/saved it, but optimizeImage might support it?)
        // optimizeImage uses Intervention which might not handle SVGs well depending on driver.
        // Usually we don't optimize SVGs with ImageManager.
        if ($optimize && str_starts_with($file->getMimeType(), 'image/') && $file->getMimeType() !== 'image/svg+xml') {
            $this->optimizeImage($fullPath);
        }

        $media = Media::create([
            'name' => $file->getClientOriginalName(),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'disk' => 'public',
            'path' => $path,
            'size' => filesize($fullPath),
            'folder_id' => $folderId,
            'author_id' => $authorId,
            'is_shared' => $isShared,
        ]);

        // Auto-generate thumbnail for images
        if (str_starts_with($media->mime_type, 'image/')) {
            try {
                $this->generateThumbnail($media);
            } catch (\Exception $e) {
                Log::channel('media')->warning('Auto-thumbnail generation failed: '.$e->getMessage());
            }
        }

        $this->cacheService->clearMediaCaches();

        return $media;
    }

    /**
     * Optimize an image file
     */
    public function optimizeImage(string $fullPath, int $maxWidth = 1920, int $quality = 85): bool
    {
        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            return false;
        }

        try {
            $driver = $this->getImageDriver();
            if (! $driver) {
                return false;
            }

            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($fullPath);

            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }

            $image->save($fullPath, quality: $quality);

            return true;
        } catch (\Exception $e) {
            Log::channel('media')->warning('Image optimization failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Generate thumbnail for media
     */
    public function generateThumbnail(Media $media, int $width = 300, int $height = 300): ?string
    {
        $fullPath = Storage::disk($media->disk)->path($media->path);

        // Create thumbnails directory
        $thumbnailDir = Storage::disk($media->disk)->path('media/thumbnails');
        if (! is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        $fileName = pathinfo($media->path, PATHINFO_FILENAME);
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);

        // SVG files get converted to PNG for thumbnail
        $isSvg = $media->mime_type === 'image/svg+xml' || strtolower($extension) === 'svg';
        $thumbnailExtension = $isSvg ? 'png' : $extension;
        $thumbnailPath = 'media/thumbnails/'.$fileName.'_thumb.'.$thumbnailExtension;
        $thumbnailFullPath = Storage::disk($media->disk)->path($thumbnailPath);

        // Handle SVG with Imagick
        if ($isSvg && extension_loaded('imagick') && class_exists('Imagick')) {
            try {
                $imagick = new \Imagick;
                $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
                $imagick->readImage($fullPath);
                $imagick->setImageFormat('png');
                $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
                $imagick->writeImage($thumbnailFullPath);
                $imagick->clear();
                $imagick->destroy();

                return $thumbnailPath;
            } catch (\Exception $e) {
                Log::channel('media')->warning('SVG thumbnail generation failed: '.$e->getMessage());
            }
        }

        // Use Intervention Image for raster images
        $driver = $this->getImageDriver();
        if (! $driver) {
            return null;
        }

        try {
            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($fullPath);
            $image->cover($width, $height);

            if ($isSvg) {
                $image->toPng()->save($thumbnailFullPath);
            } else {
                $image->save($thumbnailFullPath, quality: 85);
            }

            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::channel('media')->warning('Thumbnail generation failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Resize an image
     */
    public function resize(Media $media, int $width, ?int $height = null, int $quality = 85): bool
    {
        $driver = $this->getImageDriver();
        if (! $driver) {
            return false;
        }

        try {
            $fullPath = Storage::disk($media->disk)->path($media->path);
            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($fullPath);

            if ($height) {
                $image->resize($width, $height);
            } else {
                $image->scale(width: $width);
            }

            $image->save($fullPath, quality: $quality);

            // Update file size in database
            $media->update(['size' => filesize($fullPath)]);

            return true;
        } catch (\Exception $e) {
            Log::channel('media')->error('Image resize failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Delete media (soft delete by default, keeps physical files)
     */
    public function delete(Media $media, bool $permanent = false): void
    {
        if ($permanent) {
            $this->forceDelete($media);

            return;
        }

        // Soft delete - just mark in DB
        // We keep physical files and variants for persistence
        $media->delete();
        $this->cacheService->clearMediaCaches();
    }

    /**
     * Permanently delete media and all its physical files
     */
    public function forceDelete(Media $media): void
    {
        $this->deleteVariants($media);
        Storage::disk($media->disk)->delete($media->path);

        // Delete usages
        $media->usages()->delete();

        // Force delete the model
        $media->forceDelete();
        $this->cacheService->clearMediaCaches();
    }

    /**
     * Restore a soft-deleted media item
     */
    public function restore(int $mediaId): ?Media
    {
        $media = Media::onlyTrashed()->find($mediaId);
        if ($media) {
            $media->restore();
            $this->cacheService->clearMediaCaches();

            return $media;
        }

        return null;
    }

    /**
     * Delete all media variants (thumbnails, resized versions)
     */
    public function deleteVariants(Media $media): void
    {
        $fileName = pathinfo($media->path, PATHINFO_FILENAME);
        $extension = pathinfo($media->path, PATHINFO_EXTENSION);

        // Delete thumbnail
        $thumbnailPath = 'media/thumbnails/'.$fileName.'_thumb.'.$extension;
        if (Storage::disk($media->disk)->exists($thumbnailPath)) {
            Storage::disk($media->disk)->delete($thumbnailPath);
        }

        // Delete PNG thumbnail for SVG
        $pngThumbnailPath = 'media/thumbnails/'.$fileName.'_thumb.png';
        if (Storage::disk($media->disk)->exists($pngThumbnailPath)) {
            Storage::disk($media->disk)->delete($pngThumbnailPath);
        }

        // Delete sized variants
        foreach (['small', 'medium', 'large'] as $size) {
            $sizePath = str_replace('/media/', "/media/{$size}/", $media->path);
            if (Storage::disk($media->disk)->exists($sizePath)) {
                Storage::disk($media->disk)->delete($sizePath);
            }
        }
    }

    /**
     * Perform bulk action on media
     */
    public function bulkAction(string $action, array $mediaIds, ?int $folderId = null, ?string $altText = null): int
    {
        $query = Media::withTrashed();
        if ($action === 'restore') {
            $query->onlyTrashed();
        }

        $media = $query->whereIn('id', $mediaIds)->get();

        foreach ($media as $item) {
            switch ($action) {
                case 'delete':
                    $this->delete($item, false);
                    break;
                case 'delete_permanent':
                    $this->delete($item, true);
                    break;
                case 'restore':
                    $item->restore();
                    break;
                case 'move_folder':
                case 'move':
                    $item->update(['folder_id' => $folderId]);
                    break;
                case 'update_alt':
                    $item->update(['alt' => $altText ?? '']);
                    break;
            }
        }

        $this->cacheService->clearMediaCaches();

        return $media->count();
    }

    /**
     * Create ZIP download from multiple media
     */
    public function createZip(array $mediaIds): ?string
    {
        $media = Media::withTrashed()->whereIn('id', $mediaIds)->get();

        if ($media->isEmpty()) {
            return null;
        }

        $zipFileName = 'media-'.now()->format('Y-m-d-His').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        foreach ($media as $item) {
            $filePath = Storage::disk($item->disk)->path($item->path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $item->file_name ?: $item->name);
            }
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Get usage information for media
     */
    public function getUsageInfo(Media $media): array
    {
        $usages = $media->usages()->get();

        return $usages->map(function ($usage) {
            $model = null;
            try {
                $modelClass = $usage->model_type;
                if (class_exists($modelClass)) {
                    $model = $modelClass::find($usage->model_id);
                }
            } catch (\Exception $e) {
                Log::channel('media')->warning('Failed to load model for usage: '.$e->getMessage());
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
                    'type' => class_basename($usage->model_type),
                ],
                'created_at' => $usage->created_at,
            ];
        })->toArray();
    }

    /**
     * Edit/replace image
     */
    public function editImage(Media $media, UploadedFile $imageFile, bool $saveAsNew = false): ?Media
    {
        $driver = $this->getImageDriver();
        if (! $driver) {
            return null;
        }

        try {
            $manager = new \Intervention\Image\ImageManager($driver);
            $image = $manager->read($imageFile->getRealPath());

            if ($saveAsNew) {
                $pathInfo = pathinfo($media->path);
                $newFileName = $pathInfo['filename'].'_edited_'.time().'.'.$pathInfo['extension'];
                $newPath = $pathInfo['dirname'].'/'.$newFileName;

                $fullPath = Storage::disk($media->disk)->path($newPath);
                $directory = dirname($fullPath);
                if (! is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                $image->save($fullPath);

                $newMedia = Media::create([
                    'name' => $pathInfo['filename'].'_edited',
                    'file_name' => $newFileName,
                    'path' => $newPath,
                    'mime_type' => $media->mime_type,
                    'size' => filesize($fullPath),
                    'disk' => $media->disk,
                    'folder_id' => $media->folder_id,
                    'alt' => $media->alt,
                    'description' => $media->description,
                ]);

                try {
                    $this->generateThumbnail($newMedia);
                } catch (\Exception $e) {
                    Log::channel('media')->warning('Thumbnail generation failed for edited image: '.$e->getMessage());
                }

                return $newMedia;
            }

            // Overwrite existing
            $fullPath = Storage::disk($media->disk)->path($media->path);
            $image->save($fullPath);
            $media->update(['size' => filesize($fullPath)]);

            try {
                $this->generateThumbnail($media);
            } catch (\Exception $e) {
                Log::channel('media')->warning('Thumbnail regeneration failed: '.$e->getMessage());
            }

            return $media->fresh();
        } catch (\Exception $e) {
            Log::channel('media')->error('Image editing failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get the appropriate image driver
     */
    protected function getImageDriver(): ?object
    {
        if (! class_exists(\Intervention\Image\ImageManager::class)) {
            return null;
        }

        if (extension_loaded('gd')) {
            return new \Intervention\Image\Drivers\Gd\Driver;
        }

        if (extension_loaded('imagick')) {
            return new \Intervention\Image\Drivers\Imagick\Driver;
        }

        return null;
    }

    /**
     * Check if image processing is available
     */
    public function isImageProcessingAvailable(): bool
    {
        return $this->getImageDriver() !== null;
    }

    /**
     * Scan storage for files not in database
     *
     * @param  string  $disk
     * @param  string  $path
     * @return array
     */
    public function scan(string $disk = 'public', string $path = 'media'): array
    {
        $stats = [
            'scanned' => 0,
            'added' => 0,
            'errors' => 0,
        ];

        try {
            // Get all files recursively
            $files = Storage::disk($disk)->allFiles($path);

            foreach ($files as $filePath) {
                // Skip hidden files or temporary files
                if (str_starts_with(basename($filePath), '.')) {
                    continue;
                }

                // Skip thumbnails and variants directory
                if (str_contains($filePath, '/thumbnails/') || str_contains($filePath, '/variants/')) {
                    continue;
                }
                
                // Skip if already exists in DB
                // Use relative path matching (exact match)
                $exists = Media::where('path', $filePath)
                    ->orWhere('path', '/'.$filePath) // legacy paths might have leading slash
                    ->exists();

                if ($exists) {
                    $stats['scanned']++;
                    continue;
                }

                // File is missing in DB, add it
                try {
                    $stats['scanned']++;
                    $fileSize = Storage::disk($disk)->size($filePath);
                    $mimeType = Storage::disk($disk)->mimeType($filePath);
                    $fileName = basename($filePath);

                    // Determine folder ID from path structure?
                    // For now, scan puts everything in root or we could map folders.
                    // Let's keep it simple: just register the file.
                    // Future: map subdirectories to MediaFolder models.

                    $media = Media::create([
                        'name' => pathinfo($fileName, PATHINFO_FILENAME),
                        'file_name' => $fileName,
                        'mime_type' => $mimeType,
                        'disk' => $disk,
                        'path' => $filePath, // Store relative path without leading slash
                        'size' => $fileSize,
                        'folder_id' => null, // Or try to find folder by name?
                        'author_id' => null, // System imported
                        'is_shared' => true, // Imported files usually visible to all
                    ]);

                    $stats['added']++;

                    // Generate thumbnail if image
                    if (str_starts_with($mimeType, 'image/')) {
                        try {
                            $this->generateThumbnail($media);
                        } catch (\Exception $e) {
                            // Ignore thumbnail error
                        }
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::channel('media')->error("Failed to import file during scan: {$filePath} - " . $e->getMessage());
                    }
            }
        } catch (\Exception $e) {
            Log::channel('media')->error("Media scan failed: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Sanitize SVG file content
     */
    protected function sanitizeSvg(string $filePath): void
    {
        if (! class_exists(\enshrined\svgSanitize\Sanitizer::class)) {
            Log::channel('media')->warning('SVG Sanitizer class not found. Skipping sanitization.');
            return;
        }

        try {
            $sanitizer = new \enshrined\svgSanitize\Sanitizer();
            $sanitizer->removeRemoteReferences(true);
            
            $content = file_get_contents($filePath);
            $cleanContent = $sanitizer->sanitize($content);
            
            file_put_contents($filePath, $cleanContent);
        } catch (\Exception $e) {
            Log::channel('media')->error('SVG sanitization failed: '.$e->getMessage());
            // We do not stop the upload, but maybe we should?
            // For now, log the error.
        }
    }
}
