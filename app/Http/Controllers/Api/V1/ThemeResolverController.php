<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ThemeResolver;
use Illuminate\Http\Request;

class ThemeResolverController extends Controller
{
    protected $resolver;

    public function __construct(ThemeResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function resolve(Request $request)
    {
        $validated = $request->validate([
            'route_name' => 'nullable|string',
            'url' => 'nullable|string',
            'post_type' => 'nullable|string',
            'is_home' => 'boolean',
            'is_404' => 'boolean',
        ]);

        $template = $this->resolver->resolve($validated);

        if (!$template) {
            return response()->json(['data' => null]);
        }

        return response()->json(['data' => $template]);
    }
}
