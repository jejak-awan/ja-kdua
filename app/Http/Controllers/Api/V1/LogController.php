<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends BaseApiController
{
    public function index(Request $request)
    {
        $logPath = storage_path('logs');
        $files = [];

        if (File::isDirectory($logPath)) {
            $filesInDir = File::files($logPath);
            foreach ($filesInDir as $file) {
                if ($file->getExtension() === 'log') {
                    $files[] = [
                        'name' => $file->getFilename(),
                        'size' => $file->getSize(),
                        'modified' => $file->getMTime(),
                    ];
                }
            }
        }

        // Sort by modified date desc
        usort($files, function ($a, $b) {
            return $b['modified'] <=> $a['modified'];
        });

        return $this->success($files, 'Log files retrieved successfully');
    }

    public function show($filename)
    {
        $logFile = storage_path('logs/'.basename($filename));

        if (! File::exists($logFile)) {
            return $this->notFound('Log file');
        }

        // Limit content to last 2MB to avoid huge payload
        $content = $this->tailFile($logFile, 2000);

        return $this->success(['content' => $content], 'Log content retrieved successfully');
    }

    public function download($filename)
    {
        $logFile = storage_path('logs/'.basename($filename));

        if (File::exists($logFile)) {
            return response()->download($logFile, basename($filename));
        }

        return $this->notFound('Log file');
    }

    public function clear(Request $request)
    {
        // Default to laravel.log or specific file
        $filename = $request->input('filename', 'laravel.log');
        $logFile = storage_path('logs/'.basename($filename));

        if (File::exists($logFile)) {
            File::put($logFile, '');

            return $this->success(null, 'Log file cleared successfully');
        }

        return $this->notFound('Log file');
    }

    protected function tailFile($filepath, $lines = 100)
    {
        // Simple file get for now, or sophisticated tail logic
        // Since admin might want to see whole file but pagination is hard with text files
        // We will return the last 100 KB text

        $content = File::get($filepath);
        // If too large, truncate
        if (strlen($content) > 1024 * 500) { // 500KB limit
            return '... (File too large, showing last 500KB) ...'."\n".substr($content, -1024 * 500);
        }

        return $content;
    }
}
