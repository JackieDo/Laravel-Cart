<?php

namespace Jackiedo\Cart\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * The Cart facade.
 *
 * @package Jackiedo\Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cart';
    }
}
