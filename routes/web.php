<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

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
            | Users
            |--------------------------------------------------------------------------
            */
            Route::prefix('users')
                ->name('users.')
                ->controller(UserController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{user}/edit', 'edit')->name('edit');
                    Route::put('{user}', 'update')->name('update');
                    Route::delete('{user}', 'destroy')->name('destroy');
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
            
            /*
            |--------------------------------------------------------------------------
            | Units (Cross-Tenant)
            |--------------------------------------------------------------------------
            */
            Route::prefix('units')
                ->name('units.')
                ->controller(UnitController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{tenant}/{slug}/edit', 'edit')->name('edit');
                    Route::put('{tenant}/{slug}', 'update')->name('update');
                    Route::delete('{tenant}/{slug}', 'destroy')->name('destroy');
                });
        });

    });
}

require __DIR__.'/settings.php';
