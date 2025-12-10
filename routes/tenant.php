<?php

declare(strict_types=1);

use App\Http\Controllers\TenantPageController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/', [TenantPageController::class, 'index'])
        ->name('tenant.home');

    Route::get('/about', [TenantPageController::class, 'about'])
        ->name('tenant.about');

    Route::get('/contact', [TenantPageController::class, 'contact'])
        ->name('tenant.contact');

    Route::get('/faq', [TenantPageController::class, 'faq'])
        ->name('tenant.faq');

    Route::get('/privacy-policy', [TenantPageController::class, 'privacyPolicy'])
        ->name('tenant.privacy-policy');

    Route::get('/cancellation-policy', [TenantPageController::class, 'cancellationPolicy'])
        ->name('tenant.cancellation-policy');

    Route::get('/terms-and-conditions', [TenantPageController::class, 'termsAndConditions'])
        ->name('tenant.terms-and-conditions');

    // ============================================================
    // DYNAMIC CUSTOM ROUTES - Tenant-specific pages
    // ============================================================

    /**
     * Dynamic page route handler
     * 
     * This catches all other routes and checks if tenant has that page.
     * If page doesn't exist for current tenant → 404
     * 
     * Examples:
     * - /dive-courses → renders DiveCourses.vue (if exists for this tenant)
     * - /weddings → renders Weddings.vue (if exists for this tenant)
     * - /room-and-suites → renders RoomAndSuites.vue (if exists)
     * 
     * Naming convention:
     * - URL: kebab-case (dive-courses, rooms-and-suites)
     * - Vue Component: PascalCase (DiveCourses.vue, RoomsAndSuites.vue)
     */

    // Nested route handler (must be defined BEFORE single-level route)
    // Handles: /dining/bali-tower-bistro → dining/BaliTowerBistro.vue
    Route::get('/{parent}/{child}', [TenantPageController::class, 'showNested'])
        ->name('tenant.page.nested')
        ->where([
            'parent' => '[a-z0-9]+(?:-[a-z0-9]+)*',
            'child' => '[a-z0-9]+(?:-[a-z0-9]+)*'
        ]);

    // Single-level route handler
    Route::get('/{slug}', [TenantPageController::class, 'show'])
        ->name('tenant.page')
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*'); // kebab-case only

    // ============================================================
    // ALTERNATIVE: Explicit Custom Routes (Optional)
    // ============================================================

    /**
     * If you prefer explicit routes instead of dynamic routing,
     * uncomment and customize these routes per tenant type:
     */

    /*
    // Diving-specific routes
    Route::get('/dive-courses', [TenantPageController::class, 'show'])
        ->defaults('slug', 'dive-courses')
        ->name('tenant.page.dive-courses');
    
    Route::get('/dive-sites', [TenantPageController::class, 'show'])
        ->defaults('slug', 'dive-sites')
        ->name('tenant.page.dive-sites');
    
    // Hotel-specific routes
    Route::get('/rooms-and-suites', [TenantPageController::class, 'show'])
        ->defaults('slug', 'rooms-and-suites')
        ->name('tenant.page.rooms-and-suites');
    
    Route::get('/weddings', [TenantPageController::class, 'show'])
        ->defaults('slug', 'weddings')
        ->name('tenant.page.weddings');
    
    // Tourism-specific routes
    Route::get('/tour-packages', [TenantPageController::class, 'show'])
        ->defaults('slug', 'tour-packages')
        ->name('tenant.page.tour-packages');
    */
});
