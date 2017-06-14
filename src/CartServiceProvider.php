<?php namespace Jackiedo\Cart;

use Illuminate\Support\ServiceProvider;

/**
 * The CartServiceProvider class
 *
 * @package Jackiedo\Cart
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
        // Bootstrap handles
        $this->configHandle();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cart', function ($app) {
            return new Cart($app['session'], $app['events']);
        });
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

    /**
     * Loading and publishing package's config
     *
     * @return void
     */
    protected function configHandle()
    {
        $packageConfigPath = __DIR__.'/Config/config.php';
        $appConfigPath     = config_path('cart.php');

        $this->mergeConfigFrom($packageConfigPath, 'cart');

        $this->publishes([
            $packageConfigPath => $appConfigPath,
        ], 'config');
    }
}
