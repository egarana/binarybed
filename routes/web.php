<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| Main Domain Routes
|--------------------------------------------------------------------------
|
| Routes for the main binarybeds.com domain.
|
*/
Route::domain('binarybeds.com')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome');
    })->name('home');
});

/*
|--------------------------------------------------------------------------
| Admin Domain Routes
|--------------------------------------------------------------------------
|
| Routes for the admin subdomain: admin.binarybeds.com
| Includes authentication, dashboard, and tenant management.
|
*/
Route::domain('admin.binarybeds.com')->group(function () {

    // Login page
    Route::get('/', [AuthenticatedSessionController::class, 'create']);

    // Protected routes (requires authentication and email verification)
    Route::middleware(['auth', 'verified'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::prefix('dashboard')
            ->name('dashboard')
            ->controller(DashboardController::class)
            ->group(function () {
                Route::get('/', 'index');
            });

        /*
        |--------------------------------------------------------------------------
        | Tenants Management
        |--------------------------------------------------------------------------
        */
        Route::prefix('tenants')
            ->name('tenants.')
            ->controller(TenantController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{tenant}/edit', 'edit')->name('edit');
                Route::put('{tenant}', 'update')->name('update');
                Route::delete('{tenant}', 'destroy')->name('destroy');
            });

        /*
        |--------------------------------------------------------------------------
        | Testing Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('test')
            ->name('test.')
            ->controller(TestController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/form', 'form')->name('form');
            });
    });
});

/*
|--------------------------------------------------------------------------
| Additional Route Files
|--------------------------------------------------------------------------
*/
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
