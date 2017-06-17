<?php namespace Jackiedo\Cart\Contracts;

/**
 * The UseCartable interface
 *
 * @package Jackiedo\Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
interface UseCartable
{
    /**
     * Get the identifier of the UseCartable item.
     *
     * @return int|string
     */
    public function getUseCartableId();

    /**
     * Get the title of the UseCartable item.
     *
     * @return string
     */
    public function getUseCartableTitle();

    /**
     * Get the price of the UseCartable item.
     *
     * @return float
     */
    public function getUseCartablePrice();

    /**
     * Add the UseCartable item to the cart
     *
     * @param  string|null  $cartInstance  The cart instance name
     * @param  int          $qty           Quantities of item want to add to the cart
     * @param  array        $options       Array of additional options, such as 'size' or 'color'
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function addToCart($cartInstance = null, $qty = 1, $options = []);

    /**
     * Determine the UseCartable item has in the cart
     *
     * @param  string|null  $cartInstance  The cart instance name
     * @param  array        $options       Array of additional options, such as 'size' or 'color'
     *
     * @return boolean
     */
    public function hasInCart($cartInstance = null, array $options = []);

    /**
     * Get all the UseCartable item in the cart
     *
     * @param  string|null  $cartInstance  The cart instance name
     *
     * @return Illuminate\Support\Collection
     */
    public function allFromCart($cartInstance = null);

    /**
     * Get the UseCartable items in the cart with given additional options
     *
     * @param  string|null  $cartInstance  The cart instance name
     * @param  array        $options       Array of additional options, such as 'size' or 'color'
     *
     * @return Illuminate\Support\Collection
     */
    public function searchInCart($cartInstance = null, array $options = []);

    /**
     * Find a model by its identifier
     *
     * @param  int  $id  The identifier of model
     *
     * @return \Illuminate\Support\Collection|static|null
     */
    public function findById($id);
}
