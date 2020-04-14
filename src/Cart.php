<?php namespace Jackiedo\Cart;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Jackiedo\Cart\Exceptions\CartInvalidHashException;

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
     * @param  UseCartable|string|int  $id       UseCartable model or Unique ID of item before insert to the cart
     * @param  string|int              $title    Name of item or the quantity of UseCartable model
     * @param  int|array               $qty      Quantities of item or options of UseCartable model
     * @param  float                   $price    Price of one item
     * @param  array                   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function add($id, $title = null, $qty = 1, $price = 0, array $options = [])
    {
        // Generate a new cart item for adding to the cart
        $cartItem = $this->genCartItem($id, $title, $qty, $price, $options);

        $cartContent = $this->getContent();

        if ($cartContent->has($cartItem->hash)) {
            // If item is already exists in the cart, we will increase qty of item
            $cartItem = $this->updateQty($cartItem->hash, $cartContent->get($cartItem->hash)->qty + $cartItem->qty);
        } else {
            // If item is not exists in the cart, we will put new item to the cart
            $this->event->fire('cart.adding', [$cartItem, $cartContent]);

            $cartContent->put($cartItem->hash, $cartItem);
            $this->updateCartSession($cartContent);

            $this->event->fire('cart.added', [$cartItem, $cartContent]);
        }

        return $cartItem;
    }

    /**
     * Update an item in the cart with the given ID.
     *
     * @param  string     $itemHash    The unique identifier of the cart item
     * @param  int|array  $attributes  New quantity of item or array of new attributes to update
     *
     * @return Jackiedo\Cart\CartItem|null
     */
    public function update($itemHash, $attributes)
    {
        if (is_array($attributes)) {
            $cartItem = $this->updateCartItem($itemHash, $attributes);
        } else {
            $cartItem = $this->updateQty($itemHash, $attributes);
        }

        return $cartItem;
    }

    /**
     * Remove an cart item with the given hash out of the cart.
     *
     * @param  string  $itemHash  The unique identifier of the cart item
     *
     * @return Jackiedo\Cart\Cart
     */
    public function remove($itemHash)
    {
        $cartContent = $this->getContent();

        if ($cartContent->has($itemHash)) {
            $cartItem = $this->get($itemHash);

            $this->event->fire('cart.removing', [$cartItem, $cartContent]);

            $cartContent->forget($itemHash);
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
     * @param  string  $itemHash  The unique identifier of the cart item
     *
     * @throws Jackiedo\Cart\Exceptions\CartInvalidHashException
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function get($itemHash)
    {
        $cartContent = $this->getContent();

        if (! $cartContent->has($itemHash)) {
            throw new CartInvalidHashException("The cart does not contain hash {$itemHash}.");
        }

        return $cartContent->get($itemHash);
    }

    /**
     * Alias of get() method
     *
     * @param  string  $itemHash  The unique identifier of the cart item
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function find($itemHash)
    {
        return $this->get($itemHash);
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
     * Search the cart items with given filter
     *
     * @param  Closure|array  $filter    A closure or an array with item's attributes
     * @param  boolean        $allScope  Indicates that the results returned must satisfy
     *                                   all the conditions of the filter at the same time
     *                                   or that only parts of the filter.
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
                    $attrIntersects = $cartItem->intersect(Arr::except($filter, 'options'));
                    $optionIntersects = $cartItem->options->intersect(Arr::get($filter, 'options', []));

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
     * @param  string  $itemHash  The unique identifier of the cart item
     * @param  int     $qty       The new quantity will be updated for cart item
     * @return Jackiedo\Cart\CartItem|null
     */
    protected function updateQty($itemHash, $qty)
    {
        return $this->updateCartItem($itemHash, ['qty' => $qty]);
    }

    /**
     * Update an existing item in the cart
     *
     * @param  string  $itemHash    The unique identifier of the cart item
     * @param  array   $attributes  The attributes will be updated for cart item
     *
     * @return Jackiedo\Cart\CartItem|null
     */
    protected function updateCartItem($itemHash, $attributes)
    {
        if (array_key_exists('qty', $attributes) && intval($attributes['qty']) <= 0) {
            $this->remove($itemHash);
            return null;
        }

        $cartContent = $this->getContent();

        $cartItem = $cartContent->get($itemHash);

        $this->event->fire('cart.updating', [$cartItem, $cartContent]);

        $cartContent->pull($itemHash);
        $cartItem->update($attributes);

        if ($cartContent->has($cartItem->hash)) {
            $existingCartItem = $this->get($cartItem->hash);
            $cartItem->update(['qty' => $existingCartItem->qty + $cartItem->qty]);
        }

        $cartContent->put($cartItem->hash, $cartItem);
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
     * @param  mixed   $id       Unique ID of item before insert to the cart
     * @param  string  $title    Name of item
     * @param  int     $qty      Number of item
     * @param  float   $price    Unit price of one item
     * @param  array   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return Jackiedo\Cart\CartItem
     */
    protected function genCartItem($id, $title, $qty, $price, array $options = [])
    {
        $cartItem = new CartItem;

        return $cartItem->init($id, $title, $qty, $price, $options);
    }
}
