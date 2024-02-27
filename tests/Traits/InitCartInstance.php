<?php

use Jackiedo\Cart\Cart;

trait InitCartInstance
{
    /**
     * Create new cart instance.
     *
     * @return Cart
     */
    protected function initCart()
    {
        return new Cart();
    }
}
