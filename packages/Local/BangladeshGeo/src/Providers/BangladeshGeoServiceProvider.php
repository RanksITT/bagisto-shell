<?php

namespace Local\BangladeshGeo\Providers;

use Illuminate\Support\ServiceProvider;
use Local\BangladeshGeo\Console\Commands\SyncBdGeoCommand;
use Local\BangladeshGeo\Console\Commands\VerifyBdGeoCommand;

class BangladeshGeoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncBdGeoCommand::class,
                VerifyBdGeoCommand::class,
            ]);
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
}
