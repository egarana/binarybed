<?php

namespace App\Http\Middleware;

use App\Models\Activity;
use App\Models\Unit;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',

            // Tenant shared data (only when in tenant context)
            // Uses lazy loading via closures - only executed when accessed
            'tenant' => fn() => $this->getTenantData(),
            'sharedResources' => fn() => $this->getSharedResources(),
        ];
    }

    /**
     * Get tenant data (lazy loaded per request)
     */
    protected function getTenantData(): ?array
    {
        if (!function_exists('tenant') || !tenant()) {
            return null;
        }

        return [
            'id' => tenant('id'),
            'name' => tenant('name'),
            'domain' => request()->getHost(),
            'type' => tenant()->type ?? null,
            'industry' => tenant()->industry ?? null,
            'location' => tenant()->location ?? null,
            'resource_routes' => tenant()->resource_routes ?? [],
        ];
    }

    /**
     * Get shared resources (units and activities) - lazy loaded per request
     */
    protected function getSharedResources(): ?array
    {
        if (!function_exists('tenant') || !tenant()) {
            return null;
        }

        return [
            'units' => Unit::select(['id', 'name', 'slug', 'created_at'])
                ->orderBy('name')
                ->get()
                ->toArray(),
            'activities' => Activity::select(['id', 'name', 'slug', 'created_at'])
                ->orderBy('name')
                ->get()
                ->toArray(),
        ];
    }
}
