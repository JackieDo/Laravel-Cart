<?php namespace Jackiedo\Cart;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Jackiedo\Cart\Exceptions\CartInvalidItemIdException;

/**
 * The Cart class.
 *
 * @package Jackiedo\Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class Cart
{

    /**
     * Default instance name
     */
    const DEFAULT_INSTANCE = 'default';

    /**
     * Session class instance
     *
     * @var Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * Event class instance
     *
     * @var Illuminate\Events\Dispatcher
     */
    protected $event;

    /**
     * Current cart instance
     *
     * @var string
     */
    protected $instance;

    /**
     * Constructor
     *
     * @param  Illuminate\Session\SessionManager       $session  Session class instance
     * @param  Illuminate\Contracts\Events\Dispatcher  $event    Event class instance
     *
     * @return void;
     */
    public function __construct(SessionManager $session, Dispatcher $event)
    {
        $this->session = $session;
        $this->event = $event;

        $this->instance(self::DEFAULT_INSTANCE);
    }

    /**
     * Set the current cart instance
     *
     * @param  string|null  $instance  Cart instance name
     *
     * @return Jackiedo\Cart\Cart
     */
    public function instance($instance = null)
    {
        $instance = $instance ?: self::DEFAULT_INSTANCE;

        $this->instance = 'cart.' . $instance;

        return $this;
    }

    /**
     * Get the current cart instance
     *
     * @return string
     */
    public function getInstance()
    {
        return str_replace('cart.', '', $this->instance);
    }

    /**
     * Add an item to the cart
     *
     * @param  string|int  $rawId    Associated model or Unique ID of item before insert to the cart
     * @param  string      $title    Name of item
     * @param  int         $qty      Quantities of item want to add to the cart
     * @param  float       $price    Price of one item
     * @param  array       $options  Array of additional options, such as 'size' or 'color'
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function add($rawId, $title = null, $qty = 1, $price = 0, array $options = [])
    {
        // Prepare a new cart item for adding
        $cartItem = $this->genCartItem($rawId, $title, $qty, $price, $options);

        $cartContent = $this->getContent();

        if ($cartContent->has($cartItem->id)) {
            // If item is already exists in the cart, we will increase qty of item
            $cartItem = $this->updateQty($cartItem->id, $cartContent->get($cartItem->id)->qty + $cartItem->qty);
        } else {
            // If item is not exists in the cart, we will put new item to the cart
            $this->event->fire('cart.adding', [$cartItem, $cartContent]);

            $cartContent->put($cartItem->id, $cartItem);
            $this->updateCartSession($cartContent);

            $this->event->fire('cart.added', [$cartItem, $cartContent]);
        }

        return $cartItem;
    }

    /**
     * Update an item in the cart with the given ID.
     *
     * @param  string     $cartItemId  ID of an item in the cart
     * @param  int|array  $attributes  New quantity of item or array of attributes to update
     *
     * @return Jackiedo\Cart\CartItem|null
     */
    public function update($cartItemId, $attributes)
    {
        if (is_array($attributes)) {
            $cartItem = $this->updateCartItem($cartItemId, $attributes);
        } else {
            $cartItem = $this->updateQty($cartItemId, $attributes);
        }

        return $cartItem;
    }

    /**
     * Remove an item in the cart with the given ID out of the cart.
     *
     * @param  string  $cartItemId  ID of an item in the cart
     *
     * @return Jackiedo\Cart\Cart
     */
    public function remove($cartItemId)
    {
        $cartContent = $this->getContent();

        if ($cartContent->has($cartItemId)) {
            $cartItem = $this->get($cartItemId);

            $this->event->fire('cart.removing', [$cartItem, $cartContent]);

            $cartContent->forget($cartItemId);
            $this->updateCartSession($cartContent);

            $this->event->fire('cart.removed', [$cartItem, $cartContent]);
        }

        return $this;
    }

    /**
     * Get cart content
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->getContent();
    }

    /**
     * Alias of all() method
     *
     * @return \Illuminate\Support\Collection
     */
    public function content()
    {
        return $this->all();
    }

    /**
     * Get an item in the cart by its ID.
     *
     * @param  string  $cartItemId  ID of an item in the cart
     *
     * @throws Jackiedo\Cart\Exceptions\CartInvalidItemIdException
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function get($cartItemId)
    {
        $cartContent = $this->getContent();

        if (! $cartContent->has($cartItemId)) {
            throw new CartInvalidItemIdException("The cart does not contain id {$cartItemId}.");
        }

        return $cartContent->get($cartItemId);
    }

    /**
     * Alias of get() method
     *
     * @param  string  $cartItemId  ID of an item in the cart
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function find($cartItemId)
    {
        return $this->get($cartItemId);
    }

    /**
     * Remove all items in the cart
     *
     * @return Jackiedo\Cart\Cart
     */
    public function destroy()
    {
        $cartContent = $this->getContent();

        $this->event->fire('cart.destroying', $cartContent);

        $this->session->remove($this->instance);

        $this->event->fire('cart.destroyed', $cartContent);

        return $this;
    }

    /**
     * Alias of destroy() method
     *
     * @return Jackiedo\Cart\Cart
     */
    public function removeAll()
    {
        return $this->destroy();
    }

    /**
     * Get the total price of all items in the cart.
     *
     * @return float
     */
    public function total()
    {
        $cartContent = $this->getContent();

        if ($cartContent->isEmpty()) {
            return 0;
        }

        $total = $cartContent->reduce(function ($total, CartItem $cartItem) {
            return $total + $cartItem->subtotal;
        }, 0);

        return $total;
    }

    /**
     * Get the number of items or quantities of all items in the cart
     *
     * @param  boolean  $totalItems  Get total quantities of all items (when false, will return the number of items)
     *
     * @return int
     */
    public function count($totalItems = true)
    {
        $cartContent = $this->getContent();

        if (! $totalItems) {
            return $cartContent->count();
        }

        return $cartContent->sum('qty');
    }

    /**
     * Get number of items in the cart.
     *
     * @return int
     */
    public function countItems()
    {
        return $this->count(false);
    }

    /**
     * Get quantities of all items in the cart.
     *
     * @return int
     */
    public function countQuantities()
    {
        return $this->count(true);
    }

    /**
     * Search if the cart has a item
     *
     * @param  \Closure|array  $filter    A closure or an array with item's attributes
     * @param  boolean         $allScope  Determine that the filter is satisfied for all
     *                                    attributes simultaneously or in combination.
     *
     * @return Illuminate\Support\Collection;
     */
    public function search($filter, $allScope = true)
    {
        switch (true) {
            case ($filter instanceof Closure):
                return $this->getContent()->filter($filter);
                break;

            case (is_array($filter) && $allScope):
                $filtered = $this->getContent()->filter(function ($cartItem) use ($filter) {
                    $found = true;

                    foreach ($filter as $filterKey => $filterValue) {
                        if ($filterKey == 'options') {
                            foreach ($filterValue as $optionKey => $optionValue) {
                                if (!$cartItem->options->has($optionKey) || $cartItem->options->{$optionKey} != $optionValue) {
                                    $found = false;
                                    break;
                                }
                            }
                        } else {
                            if (!$cartItem->has($filterKey) || $cartItem->{$filterKey} != $filterValue) {
                                $found = false;
                                break;
                            }
                        }
                    }

                    return $found;
                });

                return $filtered;
                break;

            case (is_array($filter) && !$allScope):
                $filtered = $this->getContent()->filter(function ($cartItem) use ($filter) {
                    $attrIntersects = $cartItem->intersect(array_except($filter, 'options'));
                    $optionIntersects = $cartItem->options->intersect(array_get($filter, 'options', []));

                    return (!$attrIntersects->isEmpty() || !$optionIntersects->isEmpty());
                });

                return $filtered;
                break;

            default:
                return new Collection();
                break;
        }
    }

    /**
     * Get cart content, if there is no cart content set yet, return a new empty Collection
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getContent()
    {
        $hasCartSession = $this->session->has($this->instance);

        return $hasCartSession ? $this->session->get($this->instance) : new Collection();
    }

    /**
     * Update the quantity of an existing item in the cart
     *
     * @param  string  $cartItemId  ID of an item in the cart
     * @param  int     $qty         Quantity will be updated for item
     * @return Jackiedo\Cart\CartItem|null
     */
    protected function updateQty($cartItemId, $qty)
    {
        return $this->updateCartItem($cartItemId, ['qty' => $qty]);
    }

    /**
     * Update an existing item in the cart
     *
     * @param  string  $cartItemId  ID of an item in the cart
     * @param  array   $attributes  Attributes will be updated for item
     *
     * @return Jackiedo\Cart\CartItem|null
     */
    protected function updateCartItem($cartItemId, $attributes)
    {
        if (array_key_exists('qty', $attributes) && intval($attributes['qty']) <= 0) {
            $this->remove($cartItemId);
            return null;
        }

        $cartContent = $this->getContent();

        $cartItem = $cartContent->get($cartItemId);

        $this->event->fire('cart.updating', [$cartItem, $cartContent]);

        $cartContent->pull($cartItemId);
        $cartItem->update($attributes);

        if ($cartContent->has($cartItem->id)) {
            $existingCartItem = $this->get($cartItem->id);
            $cartItem->update(['qty' => $existingCartItem->qty + $cartItem->qty]);
        }

        $cartContent->put($cartItem->id, $cartItem);
        $this->updateCartSession($cartContent);

        $this->event->fire('cart.updated', [$cartItem, $cartContent]);

        return $cartItem;
    }

    /**
     * Update the cart content in session
     *
     * @param  \Illuminate\Support\Collection|null  $cartContent  The new cart content
     *
     * @return void
     */
    protected function updateCartSession($cartContent)
    {
        $this->session->put($this->instance, $cartContent);
    }

    /**
     * Generate a cart item Object
     *
     * @param  mixed   $rawId    Unique ID of item before insert to the cart
     * @param  string  $title    Name of item
     * @param  int     $qty      Number of item
     * @param  float   $price    Unit price of one item
     * @param  array   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return Jackiedo\Cart\CartItem
     */
    protected function genCartItem($rawId, $title, $qty, $price, array $options = [])
    {
        $cartItem = new CartItem;

        return $cartItem->init($rawId, $title, $qty, $price, $options);
    }
}
