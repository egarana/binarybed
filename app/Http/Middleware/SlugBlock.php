<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SlugBlock
{
    public function handle(Request $request, Closure $next, ...$slugs)
    {
        $current = strtolower(app('domain')->slug ?? '');

        // Accept "a,b,c" or variadic ["a","b","c"]
        if (count($slugs) === 1 && str_contains($slugs[0], ',')) {
            $slugs = array_map('trim', explode(',', $slugs[0]));
        }

        $slugs = array_map('strtolower', $slugs);

        if (in_array($current, $slugs, true)) {
            abort(404); // or 403 if you prefer
        }

        return $next($request);
    }
}
