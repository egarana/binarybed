<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TemporaryUploadController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\ReservationController;
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
            | Temporary Uploads API
            |--------------------------------------------------------------------------
            */
            Route::prefix('api/uploads')
                ->name('uploads.')
                ->controller(TemporaryUploadController::class)
                ->group(function () {
                    Route::post('temp', 'store')->name('temp.store');
                    Route::delete('temp/{id}', 'destroy')->name('temp.destroy');
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
            | Features
            |--------------------------------------------------------------------------
            */
            Route::prefix('features')
                ->name('features.')
                ->controller(FeatureController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{feature}/edit', 'edit')->name('edit');
                    Route::put('{feature}', 'update')->name('update');
                    Route::delete('{feature}', 'destroy')->name('destroy');
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

                    // User attachment routes
                    Route::get('{tenant}/{slug}/users', 'users')->name('users');
                    Route::post('{tenant}/{slug}/users', 'attachUser')->name('users.attach');
                    Route::delete('{tenant}/{slug}/users/{user}', 'detachUser')->name('users.detach');
                    Route::put('{tenant}/{slug}/users/{user}', 'updateUser')->name('users.update');

                    // Rate routes
                    Route::get('{tenant}/{slug}/rates', 'rates')->name('rates');
                    Route::get('{tenant}/{slug}/rates/create', 'createRate')->name('rates.create');
                    Route::delete('{tenant}/{slug}/rates/{rate}', 'deleteRate')->name('rates.delete');
                });

            /*
            |--------------------------------------------------------------------------
            | Activities (Cross-Tenant)
            |--------------------------------------------------------------------------
            */
            Route::prefix('activities')
                ->name('activities.')
                ->controller(ActivityController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{tenant}/{slug}/edit', 'edit')->name('edit');
                    Route::put('{tenant}/{slug}', 'update')->name('update');
                    Route::delete('{tenant}/{slug}', 'destroy')->name('destroy');

                    // User attachment routes
                    Route::get('{tenant}/{slug}/users', 'users')->name('users');
                    Route::post('{tenant}/{slug}/users', 'attachUser')->name('users.attach');
                    Route::delete('{tenant}/{slug}/users/{user}', 'detachUser')->name('users.detach');
                    Route::put('{tenant}/{slug}/users/{user}', 'updateUser')->name('users.update');

                    // Rate routes
                    Route::get('{tenant}/{slug}/rates', 'rates')->name('rates');
                    Route::get('{tenant}/{slug}/rates/create', 'createRate')->name('rates.create');
                    Route::delete('{tenant}/{slug}/rates/{rate}', 'deleteRate')->name('rates.delete');
                });

            /*
            |--------------------------------------------------------------------------
            | Rates (Cross-Tenant)
            |--------------------------------------------------------------------------
            */
            Route::prefix('rates')
                ->name('rates.')
                ->controller(RateController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{tenant}/{resource}/{slug}/edit', 'edit')->name('edit');
                    Route::put('{tenant}/{resource}/{slug}', 'update')->name('update');
                    Route::delete('{tenant}/{resource}/{slug}', 'destroy')->name('destroy');
                });

            /*
            |--------------------------------------------------------------------------
            | Reservations (Cross-Tenant)
            |--------------------------------------------------------------------------
            */
            Route::prefix('reservations')
                ->name('reservations.')
                ->controller(ReservationController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('create', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('{tenant}/{code}/edit', 'edit')->name('edit');
                    Route::put('{tenant}/{code}', 'update')->name('update');
                    // Note: No delete route - reservations cannot be deleted, only cancelled via status change

                    // Item routes
                    Route::get('{tenant}/{code}/items', 'items')->name('items');
                    Route::get('{tenant}/{code}/items/create', 'createItem')->name('items.create');
                    Route::post('{tenant}/{code}/items', 'storeItem')->name('items.store');
                    Route::delete('{tenant}/{code}/items/{item}', 'cancelItem')->name('items.cancel');

                    // API endpoint for getting rates by resource
                    Route::get('{tenant}/{code}/items/rates', 'getResourceRates')->name('items.rates');
                });
        });
    });
}

require __DIR__ . '/settings.php';
