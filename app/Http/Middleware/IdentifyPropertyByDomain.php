<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class IdentifyPropertyByDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $host = $request->getHost(); // e.g. lakebaturcabin.com

        // Log::info('Incoming request from domain: ' . $host);

        $property = \App\Models\Property::where('domain', $host)->first();

        if (! $property) {
            abort(404, 'Property not found');
        }

        // Share property globally (accessible via request() or view())
        app()->instance('currentProperty', $property);

        return $next($request);
    }
}
