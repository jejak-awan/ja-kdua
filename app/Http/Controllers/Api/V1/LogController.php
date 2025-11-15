<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends BaseApiController
{
    public function index(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $lines = $request->input('lines', 100);
        $level = $request->input('level'); // error, warning, info, etc.

        if (! File::exists($logFile)) {
            return $this->success(['logs' => []], 'Log file not found');
        }

        $content = File::get($logFile);
        $logEntries = $this->parseLogFile($content, $lines, $level);

        return $this->success([
            'logs' => $logEntries,
            'total' => count($logEntries),
        ], 'Logs retrieved successfully');
    }

    public function clear()
    {
        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            File::put($logFile, '');

            return $this->success(null, 'Log file cleared successfully');
        }

        return $this->notFound('Log file');
    }

    public function download()
    {
        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            return response()->download($logFile, 'laravel.log');
        }

        return $this->notFound('Log file');
    }

    protected function parseLogFile($content, $limit = 100, $level = null)
    {
        $lines = explode("\n", $content);
        $entries = [];
        $currentEntry = '';

        foreach (array_reverse($lines) as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (.+?)\.(.+?): (.+)$/', $line, $matches)) {
                if ($currentEntry) {
                    $entry = $this->parseLogEntry($currentEntry);
                    if (! $level || $entry['level'] === $level) {
                        $entries[] = $entry;
                        if (count($entries) >= $limit) {
                            break;
                        }
                    }
                }
                $currentEntry = $line;
            } else {
                $currentEntry .= "\n".$line;
            }
        }

        if ($currentEntry && count($entries) < $limit) {
            $entry = $this->parseLogEntry($currentEntry);
            if (! $level || $entry['level'] === $level) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    protected function parseLogEntry($entry)
    {
        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (.+?)\.(.+?): (.+)$/s', $entry, $matches)) {
            return [
                'timestamp' => $matches[1],
                'environment' => $matches[2],
                'level' => strtolower($matches[3]),
                'message' => $matches[4],
                'raw' => $entry,
            ];
        }

        return [
            'timestamp' => now()->toDateTimeString(),
            'environment' => 'local',
            'level' => 'info',
            'message' => $entry,
            'raw' => $entry,
        ];
    }
}
