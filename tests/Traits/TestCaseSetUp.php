<?php

trait TestCaseSetUp
{
    /**
     * Get package providers.
     *
     * @param Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Jackiedo\Cart\CartServiceProvider'];
    }

    /**
     * Get package aliases.
     *
     * @param Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return ['Cart' => 'Jackiedo\Cart\Facades\Cart'];
    }

    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cart.none_commercial_carts', [
            'recently_viewed',
        ]);
    }
}
