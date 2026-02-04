<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function handle(Request $request, $path = null)
    {
        $fullPath = $path ? '/'.$path : $request->path();

        $redirect = Redirect::where('from_url', $fullPath)
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            $redirect->recordHit();

            return redirect($redirect->to_url, (int) $redirect->type);
        }

        abort(404);
    }
}
