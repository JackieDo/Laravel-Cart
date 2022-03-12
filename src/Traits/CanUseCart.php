<?php

namespace Jackiedo\Cart\Traits;

use Jackiedo\Cart\Cart;
use Jackiedo\Cart\Facades\Cart as CartFacade;

/**
 * The CanUseCart traits.
 *
 * @package Jackiedo\Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
trait CanUseCart
{
    /**
     * Add the UseCartable item to the cart.
     *
     * @param \Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
     * @param array                      $attributes The additional attributes
     * @param bool                       $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Item
     */
    public function addToCart($cartOrName, array $attributes = [], $withEvent = true)
    {
        $cart = ($cartOrName instanceof Cart) ? $cartOrName : CartFacade::newInstance($cartOrName);

        return $cart->addItem(array_merge($attributes, ['model' => $this]), $withEvent);
    }

    /**
     * Determines the UseCartable item has in the cart.
     *
     * @param \Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
     * @param array                      $filter     Array of additional filter
     *
     * @return bool
     */
    public function hasInCart($cartOrName, array $filter = [])
    {
        $foundInCart = $this->searchInCart($cartOrName, $filter);

        return !empty($foundInCart);
    }

    /**
     * Get all the UseCartable item in the cart.
     *
     * @param \Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
     *
     * @return array
     */
    public function allFromCart($cartOrName)
    {
        return $this->searchInCart($cartOrName);
    }

    /**
     * Get the UseCartable items in the cart with given additional options.
     *
     * @param \Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
     * @param array                      $filter     Array of additional filter
     *
     * @return array
     */
    public function searchInCart($cartOrName, array $filter = [])
    {
        $cart   = ($cartOrName instanceof Cart) ? $cartOrName : CartFacade::newInstance($cartOrName);
        $filter = array_merge($filter, [
            'id'               => $this->getUseCartableId(),
            'associated_class' => __CLASS__,
        ]);

        return $cart->getItems($filter, true);
    }

    /**
     * Get the identifier of the UseCartable item.
     *
     * @return int|string
     */
    public function getUseCartableId()
    {
        return method_exists($this, 'getKey') ? $this->getKey() : $this->id;
    }

    /**
     * Get the title of the UseCartable item.
     *
     * @return string
     */
    public function getUseCartableTitle()
    {
        if (property_exists($this, 'title')) {
            return $this->title;
        }

        if (property_exists($this, 'cartTitleField')) {
            return $this->getAttribute($this->cartTitleField);
        }

        return 'Unknown';
    }

    /**
     * Get the price of the UseCartable item.
     *
     * @return float
     */
    public function getUseCartablePrice()
    {
        if (property_exists($this, 'price')) {
            return $this->price;
        }

        if (property_exists($this, 'cartPriceField')) {
            return $this->getAttribute($this->cartPriceField);
        }

        return 0;
    }

    /**
     * Find a model by its identifier.
     *
     * @param int $id The identifier of model
     *
     * @return null|\Illuminate\Support\Collection|static
     */
    public function findById($id)
    {
        return $this->find($id);
    }
}
