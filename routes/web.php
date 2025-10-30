<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return Inertia::render('Welcome', [
                'canRegister' => Features::enabled(Features::registration()),
            ]);
        })->name('home');


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
            | Tenants
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
            });
    });
}

require __DIR__.'/settings.php';
