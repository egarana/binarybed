<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class TenantPageController extends Controller
{
    public function index(): Response
    {
        return $this->renderPage('Index');
    }

    public function about(): Response
    {
        return $this->renderPage('About');
    }

    public function contact(): Response
    {
        return $this->renderPage('Contact');
    }

    public function faq(): Response
    {
        return $this->renderPage('FAQ');
    }

    public function privacyPolicy(): Response
    {
        return $this->renderPage('PrivacyPolicy');
    }

    public function cancellationPolicy(): Response
    {
        return $this->renderPage('CancellationPolicy');
    }

    public function termsAndConditions(): Response
    {
        return $this->renderPage('TermsAndConditions');
    }

    /**
     * Get tenant slug from current tenant
     */
    protected function getTenantId(): string
    {
        return tenant('id');
    }

    /**
     * Check if tenant has specific page/route
     */
    protected function tenantHasPage(string $page): bool
    {
        $tenantSlug = $this->getTenantId();

        // Check if Vue file exists for this tenant
        $pagePath = resource_path("js/pages/tenants/pages/{$tenantSlug}/{$page}.vue");

        return file_exists($pagePath);
    }

    /**
     * Dynamic page renderer untuk custom pages per tenant
     * 
     * @param string $slug - Page slug (e.g., 'dive-courses', 'weddings')
     */
    public function show(string $slug): Response
    {
        // Check if this is a resource route first
        $resourceRoutes = $this->getResourceRoutes();

        if (isset($resourceRoutes[$slug])) {
            // This is a resource route - load data from database
            return $this->renderResourcePage($slug, $resourceRoutes[$slug]);
        }

        // Convert slug to PascalCase for Vue component
        // dive-courses → DiveCourses
        // rooms-and-suites → RoomsAndSuites
        $pageName = str($slug)
            ->kebab()
            ->replace('-', ' ')
            ->title()
            ->replace(' ', '')
            ->toString();

        return $this->renderPage($pageName);
    }

    /**
     * Get resource routes configuration from tenant data
     * 
     * @return array<string, string> Map of route slug to resource type (units|activities)
     */
    protected function getResourceRoutes(): array
    {
        // Use Stancl Tenancy's dynamic attribute access
        return tenant()->resource_routes ?? [];
    }

    /**
     * Render a resource page with data from database
     * 
     * @param string $slug - Route slug (e.g., 'accommodations', 'dive-courses')
     * @param string $resourceType - Resource type ('units' or 'activities')
     */
    protected function renderResourcePage(string $slug, string $resourceType): Response
    {
        $tenantSlug = $this->getTenantId();

        // Load data from appropriate model with all fields and relations
        $resources = match ($resourceType) {
            'units' => Unit::with(['features', 'rates', 'media'])->orderBy('name')->get(),
            'activities' => Activity::with(['features', 'rates', 'media'])->orderBy('name')->get(),
            default => collect([]),
        };

        // Convert slug to PascalCase for Vue component
        $pageName = str($slug)
            ->kebab()
            ->replace('-', ' ')
            ->title()
            ->replace(' ', '')
            ->toString();

        // Check if page exists
        if (!$this->tenantHasPage($pageName)) {
            abort(404, "Resource page '{$pageName}' not found for tenant '{$tenantSlug}'");
        }

        return Inertia::render("tenants/pages/{$tenantSlug}/{$pageName}", [
            'tenant' => [
                'id' => tenant('id'),
                'name' => tenant('name'),
                'domain' => request()->getHost(),
            ],
            'resources' => $resources,
            'resourceType' => $resourceType,
            'resourceSlug' => $slug,
        ]);
    }

    /**
     * Handle nested/two-level dynamic routes
     * 
     * Uses subfolder structure:
     * - /dining/bali-tower-bistro → dining/BaliTowerBistro.vue
     * - /courses/advanced-open-water → courses/AdvancedOpenWater.vue
     * 
     * For resource routes (tours, rooms, etc):
     * - /tours/atv-riding-adventure → loads Activity from DB
     * - /rooms/deluxe-suite → loads Unit from DB
     * 
     * @param string $parent - First level slug (e.g., 'dining', 'tours')
     * @param string $child - Second level slug (e.g., 'bali-tower-bistro', 'atv-riding-adventure')
     */
    public function showNested(string $parent, string $child): Response
    {
        // Check if parent is a resource route (e.g., 'tours' => 'activities')
        $resourceRoutes = $this->getResourceRoutes();

        if (isset($resourceRoutes[$parent])) {
            // This is a resource detail page - load from database
            return $this->renderResourceDetailPage($parent, $child, $resourceRoutes[$parent]);
        }

        // Fallback: static nested pages (e.g., dining/BaliTowerBistro.vue)
        // Convert child slug to PascalCase for component name
        // bali-tower-bistro → BaliTowerBistro
        $childPascal = str($child)
            ->kebab()
            ->replace('-', ' ')
            ->title()
            ->replace(' ', '')
            ->toString();

        // Build page path: parent/ChildComponent
        // e.g., "dining/BaliTowerBistro"
        $pageName = $parent . '/' . $childPascal;

        return $this->renderPage($pageName);
    }

    /**
     * Render a resource detail page with single resource data from database
     * 
     * @param string $parentSlug - Parent route slug (e.g., 'tours', 'rooms')
     * @param string $resourceSlug - Resource slug (e.g., 'atv-riding-adventure')
     * @param string $resourceType - Resource type ('units' or 'activities')
     */
    protected function renderResourceDetailPage(string $parentSlug, string $resourceSlug, string $resourceType): Response
    {
        $tenantSlug = $this->getTenantId();

        // Load single resource by slug with all relations for guest display
        $resource = match ($resourceType) {
            'units' => Unit::with(['features', 'rates', 'media'])
                ->where('slug', $resourceSlug)
                ->first(),
            'activities' => Activity::with(['features', 'rates', 'media'])
                ->where('slug', $resourceSlug)
                ->first(),
            default => null,
        };

        if (!$resource) {
            abort(404, "Resource '{$resourceSlug}' not found");
        }

        // Prepare common props for all render paths
        $props = [
            'tenant' => [
                'id' => tenant('id'),
                'name' => tenant('name'),
                'domain' => request()->getHost(),
            ],
            'resource' => $resource,
            'resourceType' => $resourceType,
            'parentSlug' => $parentSlug,
        ];

        // Priority 1: Check for resource-specific detail component
        // e.g., tenants/pages/lakebaturcabin/cabins/RahajengCabin.vue
        $childPascal = str($resourceSlug)
            ->kebab()
            ->replace('-', ' ')
            ->title()
            ->replace(' ', '')
            ->toString();

        $specificPage = $parentSlug . '/' . $childPascal;

        if ($this->tenantHasPage($specificPage)) {
            return Inertia::render("tenants/pages/{$tenantSlug}/{$specificPage}", $props);
        }

        // Priority 2: Check for tenant-level ProductDetail.vue
        // e.g., tenants/pages/lakebaturcabin/ProductDetail.vue
        if ($this->tenantHasPage('ProductDetail')) {
            return Inertia::render("tenants/pages/{$tenantSlug}/ProductDetail", $props);
        }

        // No detail page found - abort with helpful message
        abort(404, "No detail page found for '{$resourceSlug}'. Create ProductDetail.vue in tenant folder.");
    }

    /**
     * Helper method to render tenant page
     * 
     * @param string $pageName - Vue component name (e.g., 'About', 'DiveCourses')
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function renderPage(string $pageName): Response
    {
        $tenantSlug = $this->getTenantId();

        // Check if page exists for this tenant
        if (!$this->tenantHasPage($pageName)) {
            abort(404, "Page '{$pageName}' not found for tenant '{$tenantSlug}'");
        }

        // Render page dengan Inertia
        // Path: tenants/pages/{tenantSlug}/{PageName}
        return Inertia::render("tenants/pages/{$tenantSlug}/{$pageName}", [
            'tenant' => [
                'id' => tenant('id'),
                'name' => tenant('name'),
                'domain' => request()->getHost(),
            ],
            // Bisa tambah data lain yang dibutuhkan
            // 'page_data' => $this->getPageData($pageName),
        ]);
    }
}
