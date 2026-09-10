<?php

namespace Local\SSLCommerz\Providers;

use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class SSLCommerzServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/payment-methods.php', 'payment_methods'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/system.php', 'core'
        );
    }

    /**
     * Bootstrap services.
     *
     * SSLCommerz signs the fields it posts back exactly as it sent them, so those two routes are
     * left out of string trimming: a card issuer name with a trailing space, trimmed on the way
     * in, would fail the signature of a genuine payment.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(dirname(__DIR__).'/Routes/web.php');

        $this->loadTranslationsFrom(dirname(__DIR__).'/Resources/lang', 'sslcommerz');

        TrimStrings::skipWhen(fn (Request $request) => $request->is('sslcommerz/callback', 'sslcommerz/ipn'));
    }
}
