<?php

use Illuminate\Support\Facades\Route;
use Local\BangladeshGeo\Http\Controllers\Api\GeoController;

/*
 * Public read-only reference data — no auth, so guest checkout works. Throttled as cheap
 * insurance against someone hammering the lookups.
 */
Route::middleware(['throttle:120,1'])
    ->prefix('api/bd-geo')
    ->name('bd_geo.')
    ->group(function () {
        Route::get('bootstrap', [GeoController::class, 'bootstrap'])->name('bootstrap');
        Route::get('upazilas', [GeoController::class, 'upazilas'])->name('upazilas');
    });
