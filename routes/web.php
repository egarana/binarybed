<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\FrontEndDemoController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\PdfController;
use Inertia\Inertia;

// // FRONTEND (*.com, including *.*.com)
// Route::pattern('host', '(?:[a-z0-9-]+\.)+com');

// Exclude admin.*.com from {host}
Route::pattern('host', '(?!(admin\.))(?:[a-z0-9-]+\.)+com');

Route::domain('{host}')
    ->middleware(['check.domain:frontend'])
    ->group(function () {
        Route::get('/', [FrontEndController::class, 'index'])->name('index');
        Route::get('/about', [FrontEndController::class, 'about'])->name('about');
        Route::get('/contact', [FrontEndController::class, 'contact'])->name('contact');
        Route::get('/privacy-policy', [FrontEndController::class, 'privacyPolicy'])->name('privacy-policy');
        Route::get('/cancellation-policy', [FrontEndController::class, 'cancellationPolicy'])->name('cancellation-policy');
        Route::get('/nearby-attractions', [FrontEndController::class, 'nearbyAttractions'])->name('nearby-attractions');
        
        Route::get('/accommodations', [FrontEndController::class, 'accommodations'])
            ->middleware('slug.block:amayavillas')
            ->name('accommodations');

        Route::get('/villas', [FrontEndController::class, 'villas'])
            ->middleware('slug.block:default')
            ->name('villas');

        Route::get('/spa', [FrontEndController::class, 'spa'])
            ->middleware('slug.block:default,amayavillas')
            ->name('spa');
    });


// -------------------- ADMIN & AUTH ROUTES --------------------
Route::middleware(['check.domain:admin'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome');
    })->name('home');

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::prefix('dashboard')->name('dashboard')->controller(DashboardController::class)->group(function () {
            Route::get('/', 'index');
        });

        Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('role_or_permission:view-users');
            Route::get('create', 'create')->name('create')->middleware('role_or_permission:create-users');
            Route::post('/', 'store')->name('store')->middleware('role_or_permission:create-users');
            Route::get('{user}/edit', 'edit')->name('edit')->middleware('role_or_permission:view-users,edit-users,require-all');
            Route::put('{user}', 'update')->name('update')->middleware('role_or_permission:view-users,edit-users,require-all');
            Route::delete('{user}', 'destroy')->name('destroy')->middleware('role_or_permission:delete-users');
        });

        Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('role_or_permission:view-roles');
            Route::get('create', 'create')->name('create')->middleware('role_or_permission:create-roles');
            Route::post('/', 'store')->name('store')->middleware('role_or_permission:create-roles');
            Route::get('{role}/edit', 'edit')->name('edit')->middleware('role_or_permission:view-roles,edit-roles,require-all');
            Route::put('{role}', 'update')->name('update')->middleware('role_or_permission:view-roles,edit-roles,require-all');
            Route::delete('{role}', 'destroy')->name('destroy')->middleware('role_or_permission:delete-roles');
        });

        Route::prefix('properties')->name('properties.')->controller(PropertyController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('role_or_permission:view-properties');
            Route::get('create', 'create')->name('create')->middleware('role_or_permission:create-properties');
            Route::post('/', 'store')->name('store')->middleware('role_or_permission:create-properties');
            Route::get('{property}/edit', 'edit')->name('edit')->middleware('role_or_permission:view-properties,edit-properties,require-all');
            Route::put('{property}', 'update')->name('update')->middleware('role_or_permission:view-properties,edit-properties,require-all');
            Route::delete('{property}', 'destroy')->name('destroy')->middleware('role_or_permission:delete-properties');
        });

        Route::prefix('units')->name('units.')->controller(UnitController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('role_or_permission:view-units');
            Route::get('create', 'create')->name('create')->middleware('role_or_permission:create-units');
            Route::post('/', 'store')->name('store')->middleware('role_or_permission:create-units');
            Route::get('{unit}/edit', 'edit')->name('edit')->middleware('role_or_permission:view-units,edit-units,require-all');
            Route::put('{unit}', 'update')->name('update')->middleware('role_or_permission:view-units,edit-units,require-all');
            Route::delete('{unit}', 'destroy')->name('destroy')->middleware('role_or_permission:delete-units');
        });

        Route::prefix('rates')->name('rates.')->controller(RateController::class)->group(function () {
            Route::post('/', 'store')->name('store')->middleware('role_or_permission:create-rates');
            Route::put('{rate}', 'update')->name('update')->middleware('role_or_permission:view-rates,edit-rates,require-all');
            Route::delete('{rate}', 'destroy')->name('destroy')->middleware('role_or_permission:delete-rates');
        });

        Route::prefix('reservations')->name('reservations.')->controller(ReservationController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('role_or_permission:view-reservations');
            Route::get('create', 'create')->name('create')->middleware('role_or_permission:create-reservations');
            Route::post('/', 'store')->name('store')->middleware('role_or_permission:create-reservations');
            Route::get('{reservation}/edit', 'edit')->name('edit')->middleware('role_or_permission:view-reservations,edit-reservations,require-all');
            Route::put('{reservation}', 'update')->name('update')->middleware('role_or_permission:view-reservations,edit-reservations,require-all');
            Route::delete('{reservation}', 'destroy')->name('destroy')->middleware('role_or_permission:delete-reservations');
        });

        Route::prefix('calendar')->name('calendar.')->controller(AvailabilityController::class)->group(function () {
            Route::get('/', 'index')->name('index')->middleware('role_or_permission:view-calendar');
            Route::get('create', 'create')->name('create')->middleware('role_or_permission:create-calendar');
            Route::post('/', 'store')->name('store')->middleware('role_or_permission:create-calendar');
            Route::get('{availability}/edit', 'edit')->name('edit')->middleware('role_or_permission:view-calendar,edit-calendar,require-all');
            Route::delete('{availability}', 'destroy')->name('destroy')->middleware('role_or_permission:delete-calendar');
            Route::put('{unit}/update', 'update')->name('update')->middleware('role_or_permission:view-calendar,edit-calendar,require-all');
        });

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->middleware('role_or_permission:view-activity-logs')
            ->name('activity-logs.index');

        Route::get('/system-logs', [SystemLogController::class, 'index'])
            ->middleware('role_or_permission:view-system-logs')
            ->name('system-logs.index');

        Route::delete('/system-logs/clear', [SystemLogController::class, 'clear'])
            ->middleware('role_or_permission:view-system-logs,clear-system-logs,require-all')
            ->name('system-logs.clear');

        Route::prefix('test')->name('test')->controller(TestController::class)->group(function () {
            Route::get('/', 'index');
        });
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';