<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleOrPermission
{
    public function handle(Request $request, Closure $next, ...$abilities): Response
    {
        if (! $request->user()) {
            abort(404);
        }

        if ($request->user()->hasRole('super-admin')) {
            return $next($request);
        }

        // Check for optional flag 'require-all'
        $requireAll = false;
        if (in_array('require-all', $abilities)) {
            $requireAll = true;
            $abilities = array_diff($abilities, ['require-all']);
        }

        // If require-all: all permissions must be present
        if ($requireAll) {
            foreach ($abilities as $ability) {
                if (! $request->user()->can($ability)) {
                    abort(404);
                }
            }
            return $next($request);
        }

        // Default: allow if user has any of the permissions
        foreach ($abilities as $ability) {
            if ($request->user()->can($ability)) {
                return $next($request);
            }
        }

        abort(404);
    }
}
