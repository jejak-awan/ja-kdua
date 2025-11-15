<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function index(Request $request)
    {
        $query = Redirect::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('from_url', 'like', "%{$search}%")
                  ->orWhere('to_url', 'like', "%{$search}%");
            });
        }

        $redirects = $query->latest()->paginate(20);

        return response()->json($redirects);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_url' => 'required|string|unique:redirects,from_url',
            'to_url' => 'required|string',
            'type' => 'required|in:301,302,307,308',
            'is_active' => 'boolean',
        ]);

        // Ensure from_url starts with /
        if (!str_starts_with($validated['from_url'], '/')) {
            $validated['from_url'] = '/' . $validated['from_url'];
        }

        $redirect = Redirect::create($validated);

        return response()->json($redirect, 201);
    }

    public function show(Redirect $redirect)
    {
        return response()->json($redirect);
    }

    public function update(Request $request, Redirect $redirect)
    {
        $validated = $request->validate([
            'from_url' => 'sometimes|required|string|unique:redirects,from_url,' . $redirect->id,
            'to_url' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:301,302,307,308',
            'is_active' => 'boolean',
        ]);

        // Ensure from_url starts with /
        if (isset($validated['from_url']) && !str_starts_with($validated['from_url'], '/')) {
            $validated['from_url'] = '/' . $validated['from_url'];
        }

        $redirect->update($validated);

        return response()->json($redirect);
    }

    public function destroy(Redirect $redirect)
    {
        $redirect->delete();

        return response()->json(['message' => 'Redirect deleted successfully']);
    }

    public function statistics()
    {
        $stats = [
            'total' => Redirect::count(),
            'active' => Redirect::where('is_active', true)->count(),
            'total_hits' => Redirect::sum('hits'),
            'top_redirects' => Redirect::where('is_active', true)
                ->orderBy('hits', 'desc')
                ->limit(10)
                ->get(['from_url', 'to_url', 'hits', 'type']),
        ];

        return response()->json($stats);
    }
}

