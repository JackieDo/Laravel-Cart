<?php

namespace Jackiedo\Cart;

use Illuminate\Support\ServiceProvider;

/**
 * The CartServiceProvider class.
 *
 * @package Jackiedo\Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class CartServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $packageConfigPath = __DIR__ . '/Config/config.php';
        $appConfigPath     = config_path('cart.php');

        $this->mergeConfigFrom($packageConfigPath, 'cart');

        $this->publishes([
            $packageConfigPath => $appConfigPath,
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('cart', 'Jackiedo\Cart\Cart');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'cart',
        ];
    }
}
