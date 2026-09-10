<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Local\SSLCommerz\Http\Controllers\SSLCommerzController;

Route::controller(SSLCommerzController::class)
    ->middleware('web')
    ->prefix('sslcommerz')
    ->group(function () {
        Route::get('redirect', 'redirect')->name('sslcommerz.redirect');

        Route::post('callback', 'callback')
            ->withoutMiddleware([
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
            ])
            ->name('sslcommerz.callback');

        Route::get('complete', 'complete')->name('sslcommerz.complete');

        Route::post('ipn', 'ipn')
            ->withoutMiddleware([
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
            ])
            ->name('sslcommerz.ipn');
    });
