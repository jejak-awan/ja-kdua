<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $permissions = explode('|', $permission);
        foreach ($permissions as $perm) {
            if ($request->user()->can($perm)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
