<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SlugOnly
{
    public function handle(Request $request, Closure $next, ...$slugs)
    {
        $domain = app('domain');
        $current = strtolower($domain->slug ?? '');

        // Normalize params
        if (count($slugs) === 1 && str_contains($slugs[0], ',')) {
            $slugs = array_map('trim', explode(',', $slugs[0]));
        }

        $slugs = array_map('strtolower', $slugs);

        if (!in_array($current, $slugs, true)) {
            abort(404);
        }

        return $next($request);
    }
}
