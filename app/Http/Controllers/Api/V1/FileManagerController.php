<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\MediaSettingsHelper;
use App\Models\DeletedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileManagerController extends BaseApiController
{
    /**
     * Allowed disks for file manager operations.
     */
    protected array $allowedDisks = ['public'];

    /**
     * Validate the requested disk.
     * 
     * @param string|null $disk
     * @return string
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateDisk(?string $disk): string
    {
        $disk = $disk ?: 'public';
        
        // Superadmin bypass (optional, verify if user ID 1 policy applies here as per audit)
        if (Auth::id() === 1) {
            return $disk;
        }

        if (!in_array($disk, $this->allowedDisks)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'disk' => ["Disk '$disk' is not allowed access."],
            ]);
        }
        
        return $disk;
    }

    /**
     * Validate and normalize the path to prevent traversal.
     * 
     * @param string|null $path
     * @return string
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validatePath(?string $path): string
    {
        $path = $path ?: '';
        
        // Remove null bytes
        $path = str_replace("\0", '', $path);
        
        // Prevent directory traversal
        if (str_contains($path, '..')) {
             throw \Illuminate\Validation\ValidationException::withMessages([
                'path' => ["Invalid path detected (traversal)."],
            ]);
        }
        
        return trim($path, '/');
    }

    public function index(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to manage files');
        }

        try {
            $disk = $this->validateDisk($request->input('disk'));
            $path = $this->validatePath($request->input('path'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $files = [];
        $folders = [];

        try {
            // Normalize path happens in validatePath now
            $fullPath = Storage::disk($disk)->path($path);

            if (is_dir($fullPath)) {
                $items = scandir($fullPath);

                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }

                    $itemPath = $path ? $path.'/'.$item : $item;
                    $fullItemPath = $fullPath.'/'.$item;

                    if (is_dir($fullItemPath)) {
                        $folders[] = [
                            'name' => $item,
                            'path' => '/'.$itemPath,
                            'type' => 'folder',
                        ];
                    } else {
                        $files[] = [
                            'name' => $item,
                            'path' => '/'.$itemPath,
                            'type' => 'file',
                            'size' => filesize($fullItemPath),
                            'modified' => date('Y-m-d H:i:s', filemtime($fullItemPath)),
                            'extension' => pathinfo($item, PATHINFO_EXTENSION),
                            'url' => Storage::disk($disk)->url($itemPath),
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->error('Error reading directory: '.$e->getMessage(), 500, [], 'DIRECTORY_READ_ERROR');
        }

        return $this->success([
            'path' => $path ? '/'.$path : '/',
            'folders' => $folders,
            'files' => $files,
        ], 'Directory contents retrieved successfully');
    }

    public function download(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to download files');
        }

        try {
            $disk = $this->validateDisk($request->input('disk'));
            $path = $this->validatePath($request->input('path'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            return $this->notFound('File');
        }

        $fullPath = Storage::disk($disk)->path($path);

        if (is_dir($fullPath)) {
            return $this->downloadFolder($path, $disk);
        }

        return response()->download($fullPath);
    }

    public function upload(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to upload files');
        }

        // Get settings from MediaSettingsHelper (shared with Media component)
        $maxSize = MediaSettingsHelper::getMaxUploadSize();
        $allowedExtensions = MediaSettingsHelper::getAllowedExtensions();
        $allowedMimes = implode(',', $allowedExtensions);

        $request->validate([
            'file' => "required_without:files|file|max:{$maxSize}|mimes:{$allowedMimes}",
            'files' => 'required_without:file|array',
            'files.*' => "file|max:{$maxSize}|mimes:{$allowedMimes}",
            'path' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        try {
            $disk = $this->validateDisk($request->input('disk'));
            $path = $this->validatePath($request->input('path'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }
        $uploadedFiles = [];

        // Handle single file or multiple files
        $files = $request->hasFile('files')
            ? $request->file('files')
            : [$request->file('file')];

        foreach ($files as $file) {
            // Double-check extension is allowed (in case mimes validation is bypassed)
            $extension = strtolower($file->getClientOriginalExtension());
            if (! MediaSettingsHelper::isExtensionAllowed($extension)) {
                return $this->validationError([
                    'file' => ["File type '{$extension}' is not allowed."],
                ], 'Invalid file type');
            }

            $fileName = $file->getClientOriginalName();
            $filePath = $path ? $path.'/'.$fileName : $fileName;

            $content = file_get_contents($file);

            // Sanitize SVG
            if ($extension === 'svg' || $file->getMimeType() === 'image/svg+xml') {
                 if (class_exists(\enshrined\svgSanitize\Sanitizer::class)) {
                     try {
                        $sanitizer = new \enshrined\svgSanitize\Sanitizer();
                        $sanitizer->removeRemoteReferences(true);
                        $content = $sanitizer->sanitize($content);
                     } catch (\Exception $e) {
                         \Illuminate\Support\Facades\Log::warning('SVG sanitization failed in FileManager: '.$e->getMessage());
                     }
                 }
            }

            Storage::disk($disk)->put($filePath, $content);

            $uploadedFiles[] = [
                'path' => '/'.$filePath,
                'url' => Storage::disk($disk)->url($filePath),
                'name' => $fileName,
                'type' => 'file',
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'updated_at' => now(),
            ];
        }

        return $this->success([
            'files' => $uploadedFiles,
        ], 'Files uploaded successfully', 201);
    }

    /**
     * Move file to trash (soft delete)
     */
    public function delete(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to delete files or folders');
        }

        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
            'permanent' => 'nullable|boolean', // If true, skip trash
        ]);

        $path = trim($request->input('path'), '/');
        $disk = $request->input('disk', 'public');
        $permanent = $request->input('permanent', false);

        if (! Storage::disk($disk)->exists($path)) {
            return $this->notFound('File');
        }

        if ($permanent) {
            Storage::disk($disk)->delete($path);

            return $this->success(null, 'File permanently deleted');
        }

        // Move to trash
        $fileName = basename($path);
        $trashPath = '.trash/'.uniqid().'_'.$fileName;
        $fullPath = Storage::disk($disk)->path($path);

        // Get file info before moving
        $size = filesize($fullPath);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeType = mime_content_type($fullPath) ?: null;

        // Create trash directory if not exists
        Storage::disk($disk)->makeDirectory('.trash');

        // Move file to trash
        Storage::disk($disk)->move($path, $trashPath);

        // Sync with Media Library (Delete)
        try {
            // Find valid media
            $media = \App\Models\Media::where(function($q) use ($path) {
                $q->where('path', $path)
                  ->orWhere('path', '/'.$path)
                  ->orWhere('path', trim($path, '/'));
            })->first();

            if ($media) {
                $media->path = $trashPath; // Update path to point to trash
                $media->save();
                $media->delete(); // Soft delete
            }
        } catch (\Exception $e) {
             // Log but don't fail the file operation
             \Illuminate\Support\Facades\Log::warning('Failed to sync media delete: '.$e->getMessage());
        }

        // Record in database
        DeletedFile::create([
            'original_path' => '/'.$path,
            'trash_path' => $trashPath,
            'name' => $fileName,
            'type' => 'file',
            'size' => $size,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'deleted_by' => Auth::id(),
            'deleted_at' => now(),
        ]);

        return $this->success(null, 'File moved to trash');
    }

    /**
     * Move folder to trash (soft delete)
     */
    public function deleteFolder(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to delete folders');
        }

        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
            'permanent' => 'nullable|boolean',
        ]);

        $path = trim($request->input('path'), '/');
        $disk = $request->input('disk', 'public');
        $permanent = $request->input('permanent', false);

        $fullPath = Storage::disk($disk)->path($path);

        if (! is_dir($fullPath)) {
            return $this->notFound('Folder');
        }

        if ($permanent) {
            Storage::disk($disk)->deleteDirectory($path);

            return $this->success(null, 'Folder permanently deleted');
        }

        // Move to trash
        $folderName = basename($path);
        $trashPath = '.trash/'.uniqid().'_'.$folderName;

        // Create trash directory if not exists
        Storage::disk($disk)->makeDirectory('.trash');

        // Move folder to trash
        $trashFullPath = Storage::disk($disk)->path($trashPath);
        File::moveDirectory($fullPath, $trashFullPath);

        // Record in database
        DeletedFile::create([
            'original_path' => '/'.$path,
            'trash_path' => $trashPath,
            'name' => $folderName,
            'type' => 'folder',
            'size' => null,
            'extension' => null,
            'mime_type' => null,
            'deleted_by' => Auth::id(),
            'deleted_at' => now(),
        ]);

        return $this->success(null, 'Folder moved to trash');
    }

    public function createFolder(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to create folders');
        }

        $request->validate([
            'name' => 'required|string',
            'path' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        $name = $request->input('name');
        
        try {
            $disk = $this->validateDisk($request->input('disk'));
            $path = $this->validatePath($request->input('path'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $folderPath = $path ? $path.'/'.$name : $name;

        Storage::disk($disk)->makeDirectory($folderPath);

        return $this->success([
            'name' => $name,
            'path' => '/'.$folderPath,
            'type' => 'folder',
        ], 'Folder created successfully', 201);
    }

    /**
     * Move a file or folder to a new location
     */
    public function move(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to move files or folders');
        }

        $request->validate([
            'source' => 'required|string',
            'destination' => 'nullable|string',
            'type' => 'required|in:file,folder',
            'disk' => 'nullable|string',
        ]);

        $type = $request->input('type');

        try {
            $disk = $this->validateDisk($request->input('disk'));
            $source = $this->validatePath($request->input('source'));
            $destination = $this->validatePath($request->input('destination'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            // Get the filename/foldername from source
            $name = basename($source);
            $newPath = $destination ? $destination.'/'.$name : $name;

            // Handle duplicate names
            $newPath = $this->getUniquePath($disk, $newPath);

            if ($type === 'folder') {
                $sourcePath = Storage::disk($disk)->path($source);
                $destPath = Storage::disk($disk)->path($newPath);

                if (! is_dir($sourcePath)) {
                    return $this->notFound('Source folder');
                }

                // Use File facade for moving directories
                File::moveDirectory($sourcePath, $destPath);
            } else {
                if (! Storage::disk($disk)->exists($source)) {
                    return $this->notFound('Source file');
                }

                Storage::disk($disk)->move($source, $newPath);
            }

            return $this->success([
                'oldPath' => '/'.$source,
                'newPath' => '/'.$newPath,
            ], ($type === 'folder' ? 'Folder' : 'File').' moved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to move: '.$e->getMessage(), 500, [], 'MOVE_ERROR');
        }
    }

    /**
     * Copy a file or folder
     */
    public function copy(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to copy files or folders');
        }

        $request->validate([
            'source' => 'required|string',
            'destination' => 'nullable|string',
            'type' => 'required|in:file,folder',
            'disk' => 'nullable|string',
        ]);

        $type = $request->input('type');

        try {
            $disk = $this->validateDisk($request->input('disk'));
            $source = $this->validatePath($request->input('source'));
            $destination = $this->validatePath($request->input('destination'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            $name = basename($source);
            $newPath = $destination ? $destination.'/'.$name : $name;

            // Handle duplicate names
            $newPath = $this->getUniquePath($disk, $newPath);

            if ($type === 'folder') {
                $sourcePath = Storage::disk($disk)->path($source);
                $destPath = Storage::disk($disk)->path($newPath);

                if (! is_dir($sourcePath)) {
                    return $this->notFound('Source folder');
                }

                File::copyDirectory($sourcePath, $destPath);
            } else {
                if (! Storage::disk($disk)->exists($source)) {
                    return $this->notFound('Source file');
                }

                Storage::disk($disk)->copy($source, $newPath);
            }

            return $this->success([
                'newPath' => '/'.$newPath,
            ], ($type === 'folder' ? 'Folder' : 'File').' copied successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to copy: '.$e->getMessage(), 500, [], 'COPY_ERROR');
        }
    }

    /**
     * Helper: Get unique path if file/folder exists
     */
    private function getUniquePath($disk, $path)
    {
        if (! Storage::disk($disk)->exists($path)) {
            return $path;
        }

        $dir = dirname($path);
        $dir = $dir === '.' ? '' : $dir.'/';
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $extString = $extension ? '.'.$extension : '';

        $counter = 1;
        while (Storage::disk($disk)->exists($dir.$filename.' ('.$counter.')'.$extString)) {
            $counter++;
        }

        return $dir.$filename.' ('.$counter.')'.$extString;
    }

    /**
     * Rename a file or folder
     */
    public function rename(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to rename files or folders');
        }

        $request->validate([
            'path' => 'required|string',
            'newName' => 'required|string',
            'type' => 'required|in:file,folder',
            'disk' => 'nullable|string',
        ]);

        $newName = $request->input('newName');
        $type = $request->input('type');
        
        try {
            $disk = $this->validateDisk($request->input('disk'));
            $path = $this->validatePath($request->input('path'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }

        try {
            $parentDir = dirname($path);
            $parentDir = $parentDir === '.' ? '' : $parentDir;
            $newPath = $parentDir ? $parentDir.'/'.$newName : $newName;

            if ($type === 'folder') {
                $sourcePath = Storage::disk($disk)->path($path);
                $destPath = Storage::disk($disk)->path($newPath);

                if (! is_dir($sourcePath)) {
                    return $this->notFound('Folder');
                }

                File::moveDirectory($sourcePath, $destPath);
            } else {
                if (! Storage::disk($disk)->exists($path)) {
                    return $this->notFound('File');
                }

                Storage::disk($disk)->move($path, $newPath);
            }

            return $this->success([
                'oldPath' => '/'.$path,
                'newPath' => '/'.$newPath,
                'name' => $newName,
            ], ($type === 'folder' ? 'Folder' : 'File').' renamed successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to rename: '.$e->getMessage(), 500, [], 'RENAME_ERROR');
        }
    }

    /**
     * List all items in trash
     */
    public function trash(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to view trashed files');
        }

        $items = DeletedFile::with('deletedByUser')
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'original_path' => $item->original_path,
                    'type' => $item->type,
                    'size' => $item->size,
                    'formatted_size' => $item->formatted_size,
                    'extension' => $item->extension,
                    'deleted_at' => $item->deleted_at?->toIso8601String(),
                    'deleted_by' => $item->deletedByUser?->name ?? 'Unknown',
                ];
            });

        return $this->success([
            'items' => $items,
            'count' => $items->count(),
        ], 'Trash contents retrieved successfully');
    }

    /**
     * Restore item from trash
     */
    public function restore(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to restore files');
        }

        $request->validate([
            'id' => 'required|integer|exists:deleted_files,id',
            'disk' => 'nullable|string',
        ]);

        $disk = $request->input('disk', 'public');
        $deletedFile = DeletedFile::findOrFail($request->input('id'));

        $trashPath = $deletedFile->trash_path;
        $originalPath = trim($deletedFile->original_path, '/');

        // Check if trash file exists
        if ($deletedFile->type === 'folder') {
            $trashFullPath = Storage::disk($disk)->path($trashPath);
            if (! is_dir($trashFullPath)) {
                $deletedFile->delete();

                return $this->error('Trash item no longer exists', 404, [], 'TRASH_ITEM_NOT_FOUND');
            }

            // Check if original path is available
            $originalFullPath = Storage::disk($disk)->path($originalPath);
            if (is_dir($originalFullPath)) {
                // Generate unique path
                $originalPath = $originalPath.'_restored_'.time();
            }

            // Restore folder
            File::moveDirectory($trashFullPath, Storage::disk($disk)->path($originalPath));
        } else {
            if (! Storage::disk($disk)->exists($trashPath)) {
                $deletedFile->delete();

                return $this->error('Trash item no longer exists', 404, [], 'TRASH_ITEM_NOT_FOUND');
            }

            // Check if original path is available
            if (Storage::disk($disk)->exists($originalPath)) {
                $ext = pathinfo($originalPath, PATHINFO_EXTENSION);
                $name = pathinfo($originalPath, PATHINFO_FILENAME);
                $dir = dirname($originalPath);
                $dir = $dir === '.' ? '' : $dir.'/';
                $originalPath = $dir.$name.'_restored_'.time().'.'.$ext;
            }

            // Restore file
            Storage::disk($disk)->move($trashPath, $originalPath);
        }

        // Sync with Media Library (Restore)
        if ($deletedFile->type === 'file') {
             try {
                $media = \App\Models\Media::withTrashed()
                    ->where(function($q) use ($trashPath) {
                        $q->where('path', $trashPath)
                          ->orWhere('path', '/'.$trashPath);
                    })
                    ->first();

                if ($media) {
                    $media->path = $originalPath; // Update to restored path
                    $media->save(); // Save path change
                    $media->restore(); // Un-soft-delete
                }
            } catch (\Exception $e) {
                // Log but continue
                \Illuminate\Support\Facades\Log::warning('Failed to sync media restore: '.$e->getMessage());
            }
        }

        // Remove from database
        $deletedFile->delete();

        return $this->success([
            'restored_path' => '/'.$originalPath,
        ], 'Item restored successfully');
    }

    /**
     * Empty entire trash
     */
    public function emptyTrash(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to empty trash');
        }

        $request->validate([
            'disk' => 'nullable|string',
        ]);

        $disk = $request->input('disk', 'public');
        try {
             $disk = $this->validateDisk($disk);
        } catch (\Illuminate\Validation\ValidationException $e) {
             return $this->validationError($e->errors());
        }

        // Sync with Media Library (Delete All Trashed)
        $deletedFiles = DeletedFile::where('type', 'file')->get();
        foreach ($deletedFiles as $file) {
             try {
                $media = \App\Models\Media::withTrashed()
                    ->where(function($q) use ($file) {
                        $q->where('path', $file->trash_path)
                          ->orWhere('path', '/'.$file->trash_path);
                    })
                    ->first();

                if ($media) {
                    $media->forceDelete();
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Delete all files from .trash folder
        $trashPath = Storage::disk($disk)->path('.trash');
        if (is_dir($trashPath)) {
            File::deleteDirectory($trashPath);
            Storage::disk($disk)->makeDirectory('.trash');
        }

        // Clear database records
        $count = DeletedFile::count();
        DeletedFile::truncate();

        return $this->success([
            'deleted_count' => $count,
        ], 'Trash emptied successfully');
    }

    /**
     * Permanently delete single item from trash
     */
    public function deletePermanently(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to permanently delete files');
        }

        $request->validate([
            'id' => 'required|integer|exists:deleted_files,id',
            'disk' => 'nullable|string',
        ]);

        $disk = $request->input('disk', 'public');
        try {
             $disk = $this->validateDisk($disk);
        } catch (\Illuminate\Validation\ValidationException $e) {
             return $this->validationError($e->errors());
        }
        $deletedFile = DeletedFile::findOrFail($request->input('id'));

        $trashPath = $deletedFile->trash_path;

        // Sync with Media Library (Force Delete)
        if ($deletedFile->type === 'file') {
             try {
                $media = \App\Models\Media::withTrashed()
                    ->where(function($q) use ($trashPath) {
                        $q->where('path', $trashPath)
                          ->orWhere('path', '/'.$trashPath);
                    })
                    ->first();

                if ($media) {
                    $media->forceDelete();
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Delete from storage
        if ($deletedFile->type === 'folder') {
            $trashFullPath = Storage::disk($disk)->path($trashPath);
            if (is_dir($trashFullPath)) {
                File::deleteDirectory($trashFullPath);
            }
        } else {
            if (Storage::disk($disk)->exists($trashPath)) {
                Storage::disk($disk)->delete($trashPath);
            }
        }

        // Remove from database
        $deletedFile->delete();

        return $this->success(null, 'Item permanently deleted');
    }

    /**
     * Extract archive (zip, tar.gz, tar)
     */
    public function extract(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to extract archives');
        }

        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
        ]);

        try {
            $disk = $this->validateDisk($request->input('disk'));
            $path = $this->validatePath($request->input('path'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }
        $fullPath = Storage::disk($disk)->path($path);

        if (! file_exists($fullPath)) {
            return $this->notFound('Archive file');
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $fileName = pathinfo($path, PATHINFO_FILENAME);
        $parentDir = dirname($path);
        $parentDir = $parentDir === '.' ? '' : $parentDir;

        // Create extraction directory
        $extractDir = $parentDir ? $parentDir.'/'.$fileName : $fileName;
        $extractPath = Storage::disk($disk)->path($extractDir);

        // Handle if directory already exists
        if (is_dir($extractPath)) {
            $extractDir = $extractDir.'_'.time();
            $extractPath = Storage::disk($disk)->path($extractDir);
        }

        Storage::disk($disk)->makeDirectory($extractDir);

        try {
            if ($extension === 'zip') {
                $zip = new \ZipArchive;
                if ($zip->open($fullPath) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $filename = $zip->getNameIndex($i);
                        
                        // Prevent Zip Slip
                        // 1. Check for .. in path
                        if (str_contains($filename, '..')) {
                            $zip->close();
                             throw new \Exception("Security Violation: Zip Slip detected in entry '$filename'");
                        }
                        
                        // 2. Ensure destination is within extract dir
                        $destination = $extractPath . '/' . $filename;
                        
                        // Canonicalize path (handle if it doesn't exist yet)
                        // Note: We can't use realpath on non-existent files.
                        // We rely on '..' check above strongly.
                        
                        // Extract specific file
                        $zip->extractTo($extractPath, $filename);
                    }
                    $zip->close();
                } else {
                    return $this->error('Failed to open ZIP file', 500, [], 'EXTRACT_ERROR');
                }
            } elseif (in_array($extension, ['gz', 'tgz'])) {
                // Handle tar.gz and tgz
                $phar = new \PharData($fullPath);
                if ($extension === 'gz' || str_ends_with($path, '.tar.gz')) {
                    $phar->decompress();
                    $tarPath = str_replace(['.tar.gz', '.tgz'], '.tar', $fullPath);
                    $phar = new \PharData($tarPath);
                }
                $phar->extractTo($extractPath);
            } elseif ($extension === 'tar') {
                $phar = new \PharData($fullPath);
                $phar->extractTo($extractPath);
            } else {
                return $this->error('Unsupported archive format. Supported: zip, tar, tar.gz, tgz', 400, [], 'UNSUPPORTED_FORMAT');
            }

            return $this->success([
                'extracted_to' => '/'.$extractDir,
            ], 'Archive extracted successfully');
        } catch (\Exception $e) {
            // Clean up on failure
            if (is_dir($extractPath)) {
                File::deleteDirectory($extractPath);
            }

            return $this->error('Failed to extract: '.$e->getMessage(), 500, [], 'EXTRACT_ERROR');
        }
    }

    /**
     * Compress files/folders to ZIP archive
     */
    public function compress(Request $request)
    {
        if (! $request->user()->can('manage files')) {
            return $this->forbidden('You do not have permission to compress files');
        }

        $request->validate([
            'paths' => 'required|array|min:1',
            'paths.*' => 'string',
            'name' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        $paths = $request->input('paths');
        try {
            $disk = $this->validateDisk($request->input('disk'));
            // Validate all paths in array
            $cleanPaths = [];
            foreach ($paths as $p) {
                $cleanPaths[] = $this->validatePath($p);
            }
            $paths = $cleanPaths;
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationError($e->errors());
        }
        $archiveName = $request->input('name');

        // Determine archive name
        if (! $archiveName) {
            if (count($paths) === 1) {
                $archiveName = pathinfo(trim($paths[0], '/'), PATHINFO_FILENAME).'.zip';
            } else {
                $archiveName = 'archive_'.date('Y-m-d_His').'.zip';
            }
        }

        // Ensure .zip extension
        if (! str_ends_with(strtolower($archiveName), '.zip')) {
            $archiveName .= '.zip';
        }

        // Determine output directory (same as first item's parent)
        $firstPath = trim($paths[0], '/');
        $parentDir = dirname($firstPath);
        $parentDir = $parentDir === '.' ? '' : $parentDir;
        $archivePath = $parentDir ? $parentDir.'/'.$archiveName : $archiveName;
        $fullArchivePath = Storage::disk($disk)->path($archivePath);

        // Handle if archive already exists
        if (file_exists($fullArchivePath)) {
            $baseName = pathinfo($archiveName, PATHINFO_FILENAME);
            $archiveName = $baseName.'_'.time().'.zip';
            $archivePath = $parentDir ? $parentDir.'/'.$archiveName : $archiveName;
            $fullArchivePath = Storage::disk($disk)->path($archivePath);
        }

        try {
            $zip = new \ZipArchive;
            if ($zip->open($fullArchivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return $this->error('Failed to create ZIP archive', 500, [], 'COMPRESS_ERROR');
            }

            foreach ($paths as $path) {
                $path = trim($path, '/');
                $fullPath = Storage::disk($disk)->path($path);
                $itemName = basename($path);

                if (is_dir($fullPath)) {
                    // Add directory recursively
                    $this->addDirectoryToZip($zip, $fullPath, $itemName);
                } elseif (file_exists($fullPath)) {
                    // Add file
                    $zip->addFile($fullPath, $itemName);
                }
            }

            $zip->close();

            return $this->success([
                'archive_path' => '/'.$archivePath,
                'archive_name' => $archiveName,
            ], 'Files compressed successfully');
        } catch (\Exception $e) {
            if (file_exists($fullArchivePath)) {
                unlink($fullArchivePath);
            }

            return $this->error('Failed to compress: '.$e->getMessage(), 500, [], 'COMPRESS_ERROR');
        }
    }

    /**
     * Helper: Add directory contents to ZIP recursively
     */
    private function addDirectoryToZip(\ZipArchive $zip, string $path, string $relativePath)
    {
        $zip->addEmptyDir($relativePath);
        $files = scandir($path);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $fullPath = $path.DIRECTORY_SEPARATOR.$file;
            $localPath = $relativePath.'/'.$file;

            if (is_dir($fullPath)) {
                $this->addDirectoryToZip($zip, $fullPath, $localPath);
            } else {
                $zip->addFile($fullPath, $localPath);
            }
        }
    }
}
