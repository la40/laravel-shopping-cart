<?php

namespace Lachezargrigorov\Cart;

use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //publish the config if needed
        //php artisan vendor:publish --provider="Lachezargrigorov\Cart\CartServiceProvider" --tag="config"
        $this->publishes( [
            __DIR__ . '/../config/cart.php' => config_path( 'cart.php' ),
        ], 'config' );
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../config/cart.php', 'cart' );

        $this->app->singleton(config("cart.instance_name"),function($app)
        {
            return Cart::getInstance();
        });
    }
}
