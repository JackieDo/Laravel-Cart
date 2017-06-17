<?php namespace Jackiedo\Cart\Traits;

use Jackiedo\Cart\Facades\Cart;

/**
 * The CanUseCart traits
 *
 * @package Jackiedo\Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
trait CanUseCart
{
    /**
     * Add the UseCartable item to the cart
     *
     * @param  string|null  $cartInstance  The cart instance name
     * @param  int          $qty           Quantities of item want to add to the cart
     * @param  array        $options       Array of additional options, such as 'size' or 'color'
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function addToCart($cartInstance = null, $qty = 1, $options = [])
    {
        $id    = $this->getUseCartableId();
        $title = $this->getUseCartableTitle();
        $price = $this->getUseCartablePrice();

        return Cart::instance($cartInstance)->add($this, $qty, $options);
    }

    /**
     * Determine the UseCartable item has in the cart
     *
     * @param  string|null  $cartInstance  The cart instance name
     * @param  array        $options       Array of additional options, such as 'size' or 'color'
     *
     * @return boolean
     */
    public function hasInCart($cartInstance = null, array $options = [])
    {
        $foundInCart = $this->searchInCart($cartInstance);

        return ($foundInCart->isEmpty()) ? false : true;
    }

    /**
     * Get all the UseCartable item in the cart
     *
     * @param  string|null  $cartInstance  The cart instance name
     *
     * @return Illuminate\Support\Collection
     */
    public function allFromCart($cartInstance = null)
    {
        return $this->searchInCart($cartInstance);
    }

    /**
     * Get the UseCartable items in the cart with given additional options
     *
     * @param  string|null  $cartInstance  The cart instance name
     * @param  array        $options       Array of additional options, such as 'size' or 'color'
     *
     * @return Illuminate\Support\Collection
     */
    public function searchInCart($cartInstance = null, array $options = [])
    {
        return Cart::instance($cartInstance)->search([
            'id'         => $this->getUseCartableId(),
            'title'      => $this->getUseCartableTitle(),
            'options'    => $options,
            'associated' => __CLASS__
        ]);
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
        return property_exists($this, 'title') ? $this->title : ((property_exists($this, 'cartTitleField')) ? $this->getAttribute($this->cartTitleField) : 'Unknown');
    }

    /**
     * Get the price of the UseCartable item.
     *
     * @return float
     */
    public function getUseCartablePrice()
    {
        return property_exists($this, 'price') ? $this->price : ((property_exists($this, 'cartPriceField')) ? $this->getAttribute($this->cartPriceField) : 0);
    }

    /**
     * Find a model by its identifier
     *
     * @param  int  $id  The identifier of model
     *
     * @return \Illuminate\Support\Collection|static|null
     */
    public function findById($id)
    {
        return $this->find($id);
    }
}
