<?php namespace Jackiedo\Cart\Contracts;

/**
 * The Cartable interface
 *
 * @package Jackiedo\Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
interface Cartable
{
    /**
     * Add the Cartable item to the cart
     *
     * @param  int          $qty           Quantities of item want to add to the cart
     * @param  array        $options       Array of additional options, such as 'size' or 'color'
     * @param  string|null  $cartInstance  The cart instance name
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function addToCart($qty, $options = [], $cartInstance = null);

    /**
     * Update the Cartable item in the cart
     *
     * @param  int|array    $attributes    New quantity of item or array of attributes to update
     * @param  string|null  $cartInstance  The cart instance name
     *
     * @return Jackiedo\Cart\CartItem|null
     */
    public function updateInCart($attributes, $cartInstance = null);

    /**
     * Remove the Cartable item out of the cart
     *
     * @param  string|null  $cartInstance  The cart instance name
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public function removeFromCart($cartInstance = null);

    /**
     * Get the identifier of the Cartable item.
     *
     * @return int|string
     */
    public function getCartableId();

    /**
     * Get the title of the Cartable item.
     *
     * @return string
     */
    public function getCartableTitle();

    /**
     * Get the price of the Cartable item.
     *
     * @return float
     */
    public function getCartablePrice();
}
