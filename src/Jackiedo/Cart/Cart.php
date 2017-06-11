<?php namespace Jackiedo\Cart;

use Illuminate\Events\Dispatcher;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;

/**
 * Cart
 *
 * Adapted from https://github.com/Crinsane/LaravelShoppingcart
 *
 * @package JackieDo/Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class Cart
{

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
    protected $instance = 'cart.main';

    /**
     * The Eloquent model a cart is associated with
     *
     * @var string
     */
    protected $associatedModel;

    /**
     * An optional namespace for the associated model
     *
     * @var string
     */
    protected $associatedModelNamespace;

    /**
     * Constructor
     *
     * @param  Illuminate\Session\SessionManager  $session  Session class instance
     * @param  Illuminate\Events\Dispatcher       $event    Event class instance
     *
     * @return void;
     */
    public function __construct(SessionManager $session, Dispatcher $event)
    {
        $this->session = $session;
        $this->event = $event;
    }

    /**
     * Set the current cart instance
     *
     * @param  string  $instance  Cart instance name
     *
     * @return Jackiedo\Cart\Cart
     */
    public function instance($instance)
    {
        $this->instance = 'cart.' . $instance;

        // Return self so the method is chainable
        return $this;
    }

    /**
     * Set the associated model
     *
     * @param  string  $modelName       The name of the model
     * @param  string  $modelNamespace  The namespace of the model
     *
     * @return Jackiedo\Cart\Cart
     */
    public function associate($modelName, $modelNamespace = null)
    {
        $this->associatedModel = $modelName;
        $this->associatedModelNamespace = $modelNamespace;

        $model = !is_null($modelNamespace) ? $modelNamespace . '\\' . $modelName : $modelName;

        if (! class_exists($model)) {
            throw new Exceptions\CartUnknownModelException('Invalid associate model name. Not found class "' .$model. '".');
        }

        // Return self so the method is chainable
        return $this;
    }

    /**
     * Add a row to the cart
     *
     * @param  string|int  $id       Unique ID of the item|CartItem formated as array|Array of items
     * @param  string      $title    Name of the item
     * @param  int         $qty      Item qty to add to the cart
     * @param  float       $price    Price of one item
     * @param  array       $options  Array of additional options, such as 'size' or 'color'
     *
     * @return Jackiedo\Cart\CartItem|null
     */
    public function add($id, $title = null, $qty = null, $price = null, array $options = array())
    {
        $cart = $this->getContent();

        // Fire the cart.add event
        $this->event->fire('cart.adding', [$options, $cart]);

        $row = $this->addRow($id, $title, $qty, $price, $options);

        // Fire the cart.added event
        $this->event->fire('cart.added', [$options, $cart]);

        return $row;
    }

    /**
     * Update the quantity of one row of the cart
     *
     * @param  string     $rawId      The rawId of the item you want to update
     * @param  int|array  $attribute  New quantity of the item|Array of attributes to update
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function update($rawId, $attribute)
    {
        if (!$row = $this->get($rawId)) {
            throw new Exceptions\CartInvalidRawIDException('Item not found.');
        }

        $cart = $this->getContent();

        // Fire the cart.updating event
        $this->event->fire('cart.updating', [$row, $cart]);

        if (is_array($attribute)) {
            $raw = $this->updateAttribute($rawId, $attribute);
        } else {
            $raw = $this->updateQty($rawId, $attribute);
        }

        // Fire the cart.updated event
        $this->event->fire('cart.updated', [$row, $cart]);

        return $raw;
    }

    /**
     * Remove a row from the cart
     *
     * @param  string  $rawId  The rowid of the item
     *
     * @return boolean
     */
    public function remove($rawId)
    {
        if (!$row = $this->get($rawId)) {
            return true;
        }

        $cart = $this->getContent();

        // Fire the cart.removing event
        $this->event->fire('cart.removing', [$row, $cart]);

        $cart->forget($rawId);

        // Fire the cart.removed event
        $this->event->fire('cart.removed', [$row, $cart]);

        $this->updateCart($cart);

        return true;
    }

    /**
     * Get a row of the cart by its ID
     *
     * @param  string  $rawId  The ID of the row to fetch
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function get($rawId)
    {
        $row = $this->getContent()->get($rawId);

        return is_null($row) ? null : $row;
    }

    /**
     * Get the cart content
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->getContent();
    }

    /**
     * Get the cart content (Alias of $this->content())
     *
     * @return \Illuminate\Support\Collection
     */
    public function content()
    {
        return $this->all();
    }

    /**
     * Empty the cart
     *
     * @return boolean
     */
    public function destroy()
    {
        $cart = $this->getContent();

        // Fire the cart.destroying event
        $this->event->fire('cart.destroying', $cart);

        $this->updateCart(null);

        // Fire the cart.destroyed event
        $this->event->fire('cart.destroyed', $cart);

        return true;
    }

    /**
     * Get the price total
     *
     * @return float
     */
    public function total()
    {
        $total = 0;
        $cart = $this->getContent();

        if ($cart->isEmpty()) {
            return $total;
        }

        foreach ($cart as $row) {
            $total += $row->subtotal;
        }

        return $total;
    }

    /**
     * Get the number of items in the cart
     *
     * @param  boolean  $totalItems  Get all the items (when false, will return the number of rows)
     *
     * @return int
     */
    public function count($totalItems = true)
    {
        $cart = $this->getContent();

        if (! $totalItems) {
            return $cart->count();
        }

        $count = 0;

        foreach ($cart as $row) {
            $count += $row->qty;
        }

        return $count;
    }

    /**
     * Get rows count.
     *
     * @return int
     */
    public function countRows()
    {
        return $this->count(false);
    }

    /**
     * Search if the cart has a item
     *
     * @param  array  $search  An array with the item ID and optional options
     *
     * @return Illuminate\Support\Collection;
     */
    public function search(array $search)
    {
        $rows = new Collection();

        if (empty($search)) {
            return $rows;
        }

        foreach ($this->getContent() as $item) {
            $found = $item->search($search);
            if ($found) {
                $rows->put($item->rawId, $item);
            }
        }

        return $rows;
    }

    /**
     * Add row to the cart
     *
     * @param  string  $id       Unique ID of the item
     * @param  string  $title    Name of the item
     * @param  int     $qty      Item qty to add to the cart
     * @param  float   $price    Price of one item
     * @param  array   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return Jackiedo\Cart\CartItem
     */
    protected function addRow($id, $title, $qty, $price, array $options = array())
    {
        if (empty($id) || empty($title)) {
            throw new Exceptions\CartInvalidItemException('Invalid item id or item title argument.');
        }

        if (! is_numeric($qty) || $qty < 1) {
            throw new Exceptions\CartInvalidQtyException('Invalid item quantity argument.');
        }

        if (! is_numeric($price) || $price < 0) {
            throw new Exceptions\CartInvalidPriceException('Invalid item price argument.');
        }

        $cart = $this->getContent();

        $rawId = $this->generateRawId($id, $options);

        if ($row = $cart->get($rawId)) {
            $row = $this->updateQty($rawId, $row->qty + $qty);
        } else {
            $row = $this->insertRow($rawId, $id, $title, $qty, $price, $options);
        }

        return $row;
    }

    /**
     * Generate a unique id for the new row
     *
     * @param  string  $id       Unique ID of the item
     * @param  array   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return string
     */
    protected function generateRawId($id, $options)
    {
        ksort($options);

        return md5($id . serialize($options));
    }

    /**
     * Update the cart
     *
     * @param  \Illuminate\Support\Collection|null  $cart  The new cart content
     *
     * @return \Illuminate\Support\Collection
     */
    protected function updateCart($cart)
    {
        return $this->session->put($this->getInstance(), $cart);
    }

    /**
     * Get the carts content, if there is no cart content set yet, return a new empty Collection
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getContent()
    {
        $cart = $this->session->get($this->getInstance());

        return $cart instanceof Collection ? $cart : new Collection();
    }

    /**
     * Get the current cart instance
     *
     * @return string
     */
    protected function getInstance()
    {
        return $this->instance;
    }

    /**
     * Update a row if the rawId already exists
     *
     * @param  string  $rawId       The ID of the row to update
     * @param  array   $attributes  The quantity to add to the row
     *
     * @return Jackiedo\Cart\CartItem
     */
    protected function updateRow($rawId, $attributes)
    {
        $cart = $this->getContent();

        $row = $cart->get($rawId);

        foreach ($attributes as $key => $value) {
            if ($key == 'options') {
                $options = $row->options->merge($value);
                $row->put($key, $options);
            } else {
                $row->put($key, $value);
            }
        }

        if (count(array_intersect(array_keys($attributes), ['qty', 'price']))) {
            $row->put('subtotal', $row->qty * $row->price);
        }

        $cart->put($rawId, $row);

        return $row;
    }

    /**
     * Create a new row Object.
     *
     * @param  string  $rawId    The ID of the new row
     * @param  string  $id       Unique ID of the item
     * @param  string  $title    Name of the item
     * @param  int     $qty      Item qty to add to the cart
     * @param  float   $price    Price of one item
     * @param  array   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return Jackiedo\Cart\CartItem
     */
    protected function insertRow($rawId, $id, $title, $qty, $price, $options = array())
    {
        $newRow = $this->makeRow($rawId, $id, $title, $qty, $price, $options);

        $cart = $this->getContent();

        $cart->put($rawId, $newRow);

        $this->updateCart($cart);

        return $newRow;
    }

    /**
     * Make a row item.
     *
     * @param  string  $rawId    Raw id.
     * @param  mixed   $id       Item id.
     * @param  string  $title    Item name.
     * @param  int     $qty      Quantity.
     * @param  float   $price    Price.
     * @param  array   $options  Other options.
     *
     * @return Jackiedo\Cart\CartItem
     */
    protected function makeRow($rawId, $id, $title, $qty, $price, array $options = array())
    {
        $model = !is_null($this->associatedModelNamespace) ? $this->associatedModelNamespace . '\\' .$this->associatedModel : $this->associatedModel;
        $newRow = new CartItem([
            'rawId'      => $rawId,
            'id'         => $id,
            'title'      => $title,
            'qty'        => $qty,
            'price'      => $price,
            'subtotal'   => $qty * $price,
            'options'    => new CartItemOptions($options),
            'associated' => $model,
        ]);
        return $newRow;
    }

    /**
     * Update the quantity of a row
     *
     * @param  string  $rawId  The ID of the row
     * @param  int     $qty    The qty to add
     * @return CartItem|boolean
     */
    protected function updateQty($rawId, $qty)
    {
        if ($qty <= 0) {
            return $this->remove($rawId);
        }

        return $this->updateRow($rawId, array('qty' => $qty));
    }

    /**
     * Update an attribute of the row
     *
     * @param  string  $rawId       The ID of the row
     * @param  array   $attributes  An array of attributes to update
     * @return Jackiedo\Cart\CartItem
     */
    protected function updateAttribute($rawId, $attributes)
    {
        return $this->updateRow($rawId, $attributes);
    }
}
