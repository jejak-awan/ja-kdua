<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Models\DeletedFile;

class FileManagerController extends BaseApiController
{
    public function index(Request $request)
    {
        $path = $request->input('path', '/');
        $disk = $request->input('disk', 'public');

        $files = [];
        $folders = [];

        try {
            // Normalize path
            $path = $path === '/' ? '' : trim($path, '/');
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
                            'type' => 'directory',
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

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required_without:files|file',
            'files' => 'required_without:file|array',
            'files.*' => 'file',
            'path' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        $path = trim($request->input('path', '/'), '/');
        $disk = $request->input('disk', 'public');
        $uploadedFiles = [];

        // Handle single file or multiple files
        $files = $request->hasFile('files') 
            ? $request->file('files') 
            : [$request->file('file')];

        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = $path ? $path.'/'.$fileName : $fileName;

            Storage::disk($disk)->put($filePath, file_get_contents($file));

            $uploadedFiles[] = [
                'path' => '/'.$filePath,
                'url' => Storage::disk($disk)->url($filePath),
                'name' => $fileName,
            ];
        }

        return $this->success([
            'files' => $uploadedFiles,
        ], count($uploadedFiles) > 1 ? 'Files uploaded successfully' : 'File uploaded successfully', 201);
    }

    /**
     * Move file to trash (soft delete)
     */
    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
            'permanent' => 'nullable|boolean', // If true, skip trash
        ]);

        $path = trim($request->input('path'), '/');
        $disk = $request->input('disk', 'public');
        $permanent = $request->input('permanent', false);

        if (!Storage::disk($disk)->exists($path)) {
            return $this->notFound('File');
        }

        if ($permanent) {
            Storage::disk($disk)->delete($path);
            return $this->success(null, 'File permanently deleted');
        }

        // Move to trash
        $fileName = basename($path);
        $trashPath = '.trash/' . uniqid() . '_' . $fileName;
        $fullPath = Storage::disk($disk)->path($path);
        
        // Get file info before moving
        $size = filesize($fullPath);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeType = mime_content_type($fullPath) ?: null;

        // Create trash directory if not exists
        Storage::disk($disk)->makeDirectory('.trash');
        
        // Move file to trash
        Storage::disk($disk)->move($path, $trashPath);

        // Record in database
        DeletedFile::create([
            'original_path' => '/' . $path,
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
        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
            'permanent' => 'nullable|boolean',
        ]);

        $path = trim($request->input('path'), '/');
        $disk = $request->input('disk', 'public');
        $permanent = $request->input('permanent', false);

        $fullPath = Storage::disk($disk)->path($path);

        if (!is_dir($fullPath)) {
            return $this->notFound('Folder');
        }

        if ($permanent) {
            Storage::disk($disk)->deleteDirectory($path);
            return $this->success(null, 'Folder permanently deleted');
        }

        // Move to trash
        $folderName = basename($path);
        $trashPath = '.trash/' . uniqid() . '_' . $folderName;
        
        // Create trash directory if not exists
        Storage::disk($disk)->makeDirectory('.trash');
        
        // Move folder to trash
        $trashFullPath = Storage::disk($disk)->path($trashPath);
        File::moveDirectory($fullPath, $trashFullPath);

        // Record in database
        DeletedFile::create([
            'original_path' => '/' . $path,
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
        $request->validate([
            'name' => 'required|string',
            'path' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        $name = $request->input('name');
        $path = trim($request->input('path', '/'), '/');
        $disk = $request->input('disk', 'public');

        $folderPath = $path ? $path.'/'.$name : $name;

        Storage::disk($disk)->makeDirectory($folderPath);

        return $this->success([
            'path' => '/'.$folderPath,
        ], 'Folder created successfully', 201);
    }

    /**
     * Move a file or folder to a new location
     */
    public function move(Request $request)
    {
        $request->validate([
            'source' => 'required|string',
            'destination' => 'nullable|string',
            'type' => 'required|in:file,folder',
            'disk' => 'nullable|string',
        ]);

        $source = trim($request->input('source'), '/');
        $destination = trim($request->input('destination', ''), '/');
        $type = $request->input('type');
        $disk = $request->input('disk', 'public');

        try {
            // Get the filename/foldername from source
            $name = basename($source);
            $newPath = $destination ? $destination.'/'.$name : $name;

            if ($type === 'folder') {
                $sourcePath = Storage::disk($disk)->path($source);
                $destPath = Storage::disk($disk)->path($newPath);

                if (!is_dir($sourcePath)) {
                    return $this->notFound('Source folder');
                }

                // Use File facade for moving directories
                File::moveDirectory($sourcePath, $destPath);
            } else {
                if (!Storage::disk($disk)->exists($source)) {
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
     * Rename a file or folder
     */
    public function rename(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'newName' => 'required|string',
            'type' => 'required|in:file,folder',
            'disk' => 'nullable|string',
        ]);

        $path = trim($request->input('path'), '/');
        $newName = $request->input('newName');
        $type = $request->input('type');
        $disk = $request->input('disk', 'public');

        try {
            $parentDir = dirname($path);
            $parentDir = $parentDir === '.' ? '' : $parentDir;
            $newPath = $parentDir ? $parentDir.'/'.$newName : $newName;

            if ($type === 'folder') {
                $sourcePath = Storage::disk($disk)->path($path);
                $destPath = Storage::disk($disk)->path($newPath);

                if (!is_dir($sourcePath)) {
                    return $this->notFound('Folder');
                }

                File::moveDirectory($sourcePath, $destPath);
            } else {
                if (!Storage::disk($disk)->exists($path)) {
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
            if (!is_dir($trashFullPath)) {
                $deletedFile->delete();
                return $this->error('Trash item no longer exists', 404, [], 'TRASH_ITEM_NOT_FOUND');
            }
            
            // Check if original path is available
            $originalFullPath = Storage::disk($disk)->path($originalPath);
            if (is_dir($originalFullPath)) {
                // Generate unique path
                $originalPath = $originalPath . '_restored_' . time();
            }
            
            // Restore folder
            File::moveDirectory($trashFullPath, Storage::disk($disk)->path($originalPath));
        } else {
            if (!Storage::disk($disk)->exists($trashPath)) {
                $deletedFile->delete();
                return $this->error('Trash item no longer exists', 404, [], 'TRASH_ITEM_NOT_FOUND');
            }
            
            // Check if original path is available
            if (Storage::disk($disk)->exists($originalPath)) {
                $ext = pathinfo($originalPath, PATHINFO_EXTENSION);
                $name = pathinfo($originalPath, PATHINFO_FILENAME);
                $dir = dirname($originalPath);
                $dir = $dir === '.' ? '' : $dir . '/';
                $originalPath = $dir . $name . '_restored_' . time() . '.' . $ext;
            }
            
            // Restore file
            Storage::disk($disk)->move($trashPath, $originalPath);
        }

        // Remove from database
        $deletedFile->delete();

        return $this->success([
            'restored_path' => '/' . $originalPath,
        ], 'Item restored successfully');
    }

    /**
     * Empty entire trash
     */
    public function emptyTrash(Request $request)
    {
        $request->validate([
            'disk' => 'nullable|string',
        ]);

        $disk = $request->input('disk', 'public');

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
        $request->validate([
            'id' => 'required|integer|exists:deleted_files,id',
            'disk' => 'nullable|string',
        ]);

        $disk = $request->input('disk', 'public');
        $deletedFile = DeletedFile::findOrFail($request->input('id'));

        $trashPath = $deletedFile->trash_path;

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
}
