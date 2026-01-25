<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FrontendLogController extends Controller
{
    /**
     * Handle incoming frontend log entries.
     *
     * @param  \Illuminate\Http\Request  $request
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

        $context = [
            'url' => $validated['url'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'user_agent' => $validated['user_agent'] ?? $request->userAgent(),
            'stack' => $validated['stack'] ?? null,
            'data' => $validated['data'] ?? [],
            'ip' => $request->ip(),
        ];

        $level = $validated['level'] ?? 'error';
        $message = $validated['message'];

        // Log to specific frontend channel
        Log::channel('frontend')->log($level, "Frontend Error: {$message}", $context);

        return response()->json(['status' => 'logged']);
    }
}
