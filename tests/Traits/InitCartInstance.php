<?php

use Jackiedo\Cart\Cart;

trait InitCartInstance
{
    /**
     * Create new cart instance
     *
     * @return Jackiedo\Cart\Cart
     */
    protected function initCart()
    {
        $cart = new Cart;

        return $cart;
    }
}
