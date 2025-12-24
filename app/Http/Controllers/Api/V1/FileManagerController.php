<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

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

    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
        ]);

        $path = trim($request->input('path'), '/');
        $disk = $request->input('disk', 'public');

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
            return $this->success(null, 'File deleted successfully');
        }

        return $this->notFound('File');
    }

    public function deleteFolder(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
        ]);

        $path = trim($request->input('path'), '/');
        $disk = $request->input('disk', 'public');

        $fullPath = Storage::disk($disk)->path($path);

        if (is_dir($fullPath)) {
            Storage::disk($disk)->deleteDirectory($path);
            return $this->success(null, 'Folder deleted successfully');
        }

        return $this->notFound('Folder');
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
}
