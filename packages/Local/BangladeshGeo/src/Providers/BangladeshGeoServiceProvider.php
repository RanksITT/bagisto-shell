<?php

namespace Local\BangladeshGeo\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
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
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'bdgeo');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'bdgeo');

        Blade::componentNamespace('Local\\BangladeshGeo\\View\\Components', 'bdgeo');
        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'bdgeo');

        // Our copies of the address blades must win over Webkul's. Prepending rather than
        // appending is what makes the override take effect.
        View::prependNamespace('shop', __DIR__.'/../Resources/views/overrides/shop');

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
