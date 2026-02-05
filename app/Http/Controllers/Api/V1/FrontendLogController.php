<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrontendLogController extends BaseApiController
{
    /**
     * Handle incoming frontend log entries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'stack' => 'nullable|string',
            'url' => 'nullable|string|url',
            'user_agent' => 'nullable|string',
            'user_id' => 'nullable|integer',
            'data' => 'nullable|array',
            'level' => 'nullable|string|in:info,warning,error,critical',
        ]);

        $stack = $validated['stack'] ?? null;
        if ($stack && strlen($stack) > 3000) {
            $stack = substr($stack, 0, 3000) . "\n... (truncated by backend)";
        }

        $context = [
            'url' => $validated['url'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'user_agent' => $validated['user_agent'] ?? $request->userAgent(),
            'stack' => $stack,
            'data' => $validated['data'] ?? [],
            'ip' => $request->ip(),
        ];

        $level = $validated['level'] ?? 'error';
        $message = $validated['message'];

        // Log to specific frontend channel
        Log::channel('frontend')->log($level, "Frontend Error: {$message}", $context);

        return $this->success(null, 'logged');
    }
}
