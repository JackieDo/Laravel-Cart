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
     * Add the Cartable item to the cart
     *
     * @param  int          $qty           Quantities of item want to add to the cart
     * @param  array        $options       Array of additional options, such as 'size' or 'color'
     * @param  string|null  $cartInstance  The cart instance name
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function addToCart($qty, $options = [], $cartInstance = null)
    {
        $id    = $this->getCartableId();
        $title = $this->getCartableTitle();
        $price = $this->getCartablePrice();

        return Cart::instance($cartInstance)->associate(__CLASS__)->add($id, $title, $qty, $this->price, $options);
    }

    /**
     * Update the Cartable item in the cart
     *
     * @param  int|array    $attributes    New quantity of item or array of attributes to update
     * @param  string|null  $cartInstance  The cart instance name
     *
     * @return Jackiedo\Cart\CartItem|null
     */
    public function updateInCart($attributes, $cartInstance = null)
    {
        $id = $this->getCartableId();

        $cart = Cart::instance($cartInstance);

        $findInCart = $cart->search([
            'raw_id'     => $id,
            'associated' => __CLASS__
        ]);

        if (!$findInCart->isEmpty()) {
            $cartItemId = $findInCart->first()->id;

            return $cart->update($cartItemId, $attributes);
        }
    }

    /**
     * Remove the Cartable item out of the cart
     *
     * @param  string|null  $cartInstance  The cart instance name
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public function removeFromCart($cartInstance = null)
    {
        $id = $this->getCartableId();

        $cart = Cart::instance($cartInstance);

        $findInCart = $cart->search([
            'raw_id'     => $id,
            'associated' => __CLASS__
        ]);

        if (!$findInCart->isEmpty()) {
            $cartItemId = $findInCart->first()->id;

            $cart->remove($cartItemId);
        }

        return $this;
    }

    /**
     * Get the identifier of the Cartable item.
     *
     * @return int|string
     */
    public function getCartableId()
    {
        return method_exists($this, 'getKey') ? $this->getKey() : $this->id;
    }

    /**
     * Get the title of the Cartable item.
     *
     * @return string
     */
    public function getCartableTitle()
    {
        return property_exists($this, 'title') ? $this->title : ((property_exists($this, 'cartTitleField')) ? $this->getAttribute($this->cartTitleField) : 'Unknown');
    }

    /**
     * Get the price of the Cartable item.
     *
     * @return float
     */
    public function getCartablePrice()
    {
        return property_exists($this, 'price') ? $this->price : ((property_exists($this, 'cartPriceField')) ? $this->getAttribute($this->cartPriceField) : 0);
    }
}
