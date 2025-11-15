<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseApiController;
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
        $directories = [];

        try {
            $fullPath = Storage::disk($disk)->path($path);

            if (is_dir($fullPath)) {
                $items = scandir($fullPath);

                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;

                    $itemPath = $path === '/' ? $item : $path . '/' . $item;
                    $fullItemPath = $fullPath . '/' . $item;

                    if (is_dir($fullItemPath)) {
                        $directories[] = [
                            'name' => $item,
                            'path' => $itemPath,
                            'type' => 'directory',
                        ];
                    } else {
                        $files[] = [
                            'name' => $item,
                            'path' => $itemPath,
                            'type' => 'file',
                            'size' => filesize($fullItemPath),
                            'modified' => date('Y-m-d H:i:s', filemtime($fullItemPath)),
                            'extension' => pathinfo($item, PATHINFO_EXTENSION),
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->error('Error reading directory: ' . $e->getMessage(), 500, [], 'DIRECTORY_READ_ERROR');
        }

        return $this->success([
            'path' => $path,
            'directories' => $directories,
            'files' => $files,
        ], 'Directory contents retrieved successfully');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'path' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $request->input('path', '/');
        $disk = $request->input('disk', 'public');

        $filePath = $path === '/' ? $file->getClientOriginalName() : $path . '/' . $file->getClientOriginalName();

        Storage::disk($disk)->put($filePath, file_get_contents($file));

        return $this->success([
            'path' => $filePath,
        ], 'File uploaded successfully', 201);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'disk' => 'nullable|string',
        ]);

        $path = $request->input('path');
        $disk = $request->input('disk', 'public');

        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
            return $this->success(null, 'File deleted successfully');
        }

        return $this->notFound('File');
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'path' => 'nullable|string',
            'disk' => 'nullable|string',
        ]);

        $name = $request->input('name');
        $path = $request->input('path', '/');
        $disk = $request->input('disk', 'public');

        $folderPath = $path === '/' ? $name : $path . '/' . $name;

        Storage::disk($disk)->makeDirectory($folderPath);

        return $this->success([
            'path' => $folderPath,
        ], 'Folder created successfully', 201);
    }
}
