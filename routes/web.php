<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;

Route::domain('binarybeds.com')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Welcome');
    })->name('home');
});

Route::domain('admin.binarybeds.com')->group(function () {
    Route::get('/', [AuthenticatedSessionController::class, 'create']);

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::prefix('dashboard')->name('dashboard')->controller(DashboardController::class)->group(function () {
            Route::get('/', 'index');
        });

        Route::prefix('tenants')->name('tenants.')->controller(TenantController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{tenant}/edit', 'edit')->name('edit');
            Route::put('{tenant}', 'update')->name('update');
            Route::delete('{tenant}', 'destroy')->name('destroy');
        });
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
