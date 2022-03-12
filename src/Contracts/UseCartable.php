<?php

namespace Jackiedo\Cart\Contracts;

/**
 * The UseCartable interface.
 *
 * @package Jackiedo\Cart
 *
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
     * Add the UseCartable item to the cart.
     *
     * @param \Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
     * @param array                      $attributes The additional attributes
     * @param bool                       $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Item
     */
    public function addToCart($cartOrName, array $attributes = [], $withEvent = true);

    /**
     * Determines the UseCartable item has in the cart.
     *
     * @param \Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
     * @param array                      $filter     Array of additional filter
     *
     * @return bool
     */
    public function hasInCart($cartOrName, array $filter = []);

    /**
     * Get all the UseCartable item in the cart.
     *
     * @param \Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
     *
     * @return array
     */
    public function allFromCart($cartOrName);

    /**
     * Get the UseCartable items in the cart with given additional filter.
     *
     * @param \Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
     * @param array                      $filter     Array of additional filter
     *
     * @return array
     */
    public function searchInCart($cartOrName, array $filter = []);

    /**
     * Find a model by its identifier.
     *
     * @param int $id The identifier of model
     *
     * @return null|\Illuminate\Support\Collection|static
     */
    public function findById($id);
}
