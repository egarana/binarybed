<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
     * Handle nested/two-level dynamic routes
     * 
     * Uses subfolder structure:
     * - /dining/bali-tower-bistro → dining/BaliTowerBistro.vue
     * - /courses/advanced-open-water → courses/AdvancedOpenWater.vue
     * 
     * @param string $parent - First level slug (e.g., 'dining', 'courses')
     * @param string $child - Second level slug (e.g., 'bali-tower-bistro')
     */
    public function showNested(string $parent, string $child): Response
    {
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
