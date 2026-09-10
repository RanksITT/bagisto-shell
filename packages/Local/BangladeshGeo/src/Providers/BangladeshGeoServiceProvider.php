<?php

namespace Local\BangladeshGeo\Providers;

use Illuminate\Support\ServiceProvider;
use Local\BangladeshGeo\Console\Commands\SyncBdGeoCommand;
use Local\BangladeshGeo\Console\Commands\VerifyBdGeoCommand;
use Local\BangladeshGeo\Observers\AddressObserver;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Sales\Models\OrderAddress;

class BangladeshGeoServiceProvider extends ServiceProvider
{
    /**
     * Every concrete address model Bagisto instantiates. The base Webkul\Core\Models\Address
     * is abstract, so observing it alone would fire for nothing.
     */
    protected array $addressModels = [
        CustomerAddress::class,
        CartAddress::class,
        OrderAddress::class,
    ];

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/shop-routes.php');

        foreach ($this->addressModels as $model) {
            $model::observe(AddressObserver::class);
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncBdGeoCommand::class,
                VerifyBdGeoCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        //
    }
}
