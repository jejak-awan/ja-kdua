<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(Request $request)
    {
        $query = Webhook::query();

        if ($request->has('event')) {
            $query->where('event', $request->event);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $webhooks = $query->latest()->get();

        return response()->json($webhooks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'event' => 'required|string',
            'method' => 'nullable|in:POST,PUT,PATCH',
            'headers' => 'nullable|array',
            'payload_template' => 'nullable|array',
            'timeout' => 'integer|min:1|max:300',
            'max_retries' => 'integer|min:0|max:10',
        ]);

        $webhook = Webhook::create($validated);

        return response()->json($webhook, 201);
    }

    public function show(Webhook $webhook)
    {
        return response()->json($webhook);
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|url',
            'event' => 'sometimes|required|string',
            'method' => 'nullable|in:POST,PUT,PATCH',
            'headers' => 'nullable|array',
            'payload_template' => 'nullable|array',
            'is_active' => 'boolean',
            'timeout' => 'integer|min:1|max:300',
            'max_retries' => 'integer|min:0|max:10',
        ]);

        $webhook->update($validated);

        return response()->json($webhook);
    }

    public function destroy(Webhook $webhook)
    {
        $webhook->delete();

        return response()->json(['message' => 'Webhook deleted successfully']);
    }

    public function test(Webhook $webhook)
    {
        $testData = [
            'test' => true,
            'timestamp' => now()->toIso8601String(),
        ];

        $result = $webhook->trigger($testData);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Webhook triggered successfully' : 'Webhook failed',
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total' => Webhook::count(),
            'active' => Webhook::where('is_active', true)->count(),
            'total_success' => Webhook::sum('success_count'),
            'total_failures' => Webhook::sum('failure_count'),
            'recent_webhooks' => Webhook::whereNotNull('last_triggered_at')
                ->latest('last_triggered_at')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }
}
