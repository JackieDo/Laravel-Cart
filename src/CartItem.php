<?php namespace Jackiedo\Cart;

use Illuminate\Support\Collection;
use Jackiedo\Cart\Contracts\UseCartable;
use Jackiedo\Cart\Exceptions\CartInvalidArgumentException;
use Jackiedo\Cart\Exceptions\CartUnknownModelException;

/**
 * The CartItem class
 *
 * @package JackieDo/Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class CartItem extends Collection
{

    /**
     * Initialize a well-formed cart item instance
     *
     * @param  mixed   $rawId       Unique ID of item before insert to the cart
     * @param  string  $title       Name of item
     * @param  int     $qty         Number of item
     * @param  float   $price       Unit price of one item
     * @param  array   $options     Array of additional options, such as 'size' or 'color'
     * @param  mixed   $associated  The model or the FQN of model that will be associated
     *
     * @throws Jackiedo\Cart\Exceptions\CartInvalidArgumentException
     *
     * @return Jackiedo\Cart\CartItem;
     */
    public function init($rawId, $title, $qty, $price, array $options = [], $associated = null)
    {
        if ($rawId instanceof UseCartable) {
            list($rawId, $title, $qty, $price, $options, $associated) = $this->parseFromUseCartable($rawId, $title, $qty);
        }

        if (empty($rawId)) {
            throw new CartInvalidArgumentException("The item identifier argument is not allowed to be empty.");
        }

        if (empty($title)) {
            throw new CartInvalidArgumentException("The item title argument is not allowed to be empty.");
        }

        if (! is_numeric($qty) || $qty < 1) {
            throw new CartInvalidArgumentException("The item quantity argument must be an integer type greater than 1.");
        }

        if (! is_numeric($price) || $price < 0) {
            throw new CartInvalidArgumentException("The item quantity argument must be an float type greater than 0.");
        }

        $this->put('id', $this->genId($rawId, $associated, $options));
        $this->put('raw_id', $rawId);
        $this->put('title', $title);
        $this->put('qty', intval($qty));
        $this->put('price', floatval($price));
        $this->put('subtotal', $this->calcSubTotal($qty, $price));
        $this->put('options', new CartItemOptions($options));
        $this->put('associated', $associated);

        return $this;
    }

    /**
     * Magic accessor.
     *
     * @param  string  $property  Property name.
     *
     * @throws Jackiedo\Cart\Exceptions\CartUnknownModelException
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ($property === 'model') {
            $model = with(new $this->associated)->findById($this->raw_id);

            if (!$model) {
                throw new CartUnknownModelException("The supplied associated model from ".$this->associated." does not exist.");
            }

            return $model;
        }

        return $this->get($property);
    }

    /**
     * Update info of cart item
     *
     * @param  array  $attributes
     *
     * @return Jackiedo\Cart\CartItem
     */
    public function update(array $attributes)
    {
        // Don't allow update manually following attributes: id, raw_id, subtotal, associated
        $attributes = array_except($attributes, ['id', 'raw_id', 'subtotal', 'associated']);

        // Format data
        foreach ($attributes as $key => $value) {
            switch (true) {
                case ($key == 'options'):
                    $value = $this->options->merge($value);
                    break;

                case ($key == 'qty'):
                    $value = intval($value);
                    break;

                case ($key == 'price'):
                    $value = floatval($value);
                    break;

                default:
                    # code...
                    break;
            }

            $this->put($key, $value);
        }

        // Recalculate subtotal
        if (count(array_intersect(array_keys($attributes), ['qty', 'price'])) > 0) {
            $this->updateSubTotal();
        }

        // Regenerate ID
        if (count(array_intersect(array_keys($attributes), ['options'])) > 0) {
            $this->updateId();
        }

        return $this;
    }

    /**
     * Get data for initializing item from instance of UseCartable
     *
     * @param  object  $useCartableInstance
     * @param  int     $qty
     * @param  array   $options
     *
     * @return array
     */
    protected function parseFromUseCartable($useCartableInstance, $qty, $options)
    {
        $rawId      = $useCartableInstance->getUseCartableId();
        $title      = $useCartableInstance->getUseCartableTitle();
        $qty        = $qty ?: 1;
        $price      = $useCartableInstance->getUseCartablePrice();
        $options    = (!is_array($options)) ? [] : $options;
        $associated = get_class($useCartableInstance);

        return [$rawId, $title, $qty, $price, $options, $associated];
    }

    /**
     * Generate an unique id for the cart item
     *
     * @param  string  $rawId    Unique ID of item before insert to the cart
     * @param  array   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return string
     */
    protected function genId($rawId, $associated, $options = [])
    {
        ksort($options);
        return md5($rawId . serialize($associated) . serialize($options));
    }

    /**
     * Update ID for the cart item
     *
     * @return void
     */
    protected function updateId()
    {
        $this->put('id', $this->genId($this->raw_id, $this->associate, $this->options->all()));
    }

    /**
     * Calculate sub total price from qty and price of the cart item
     *
     * @param  int    $qty
     * @param  float  $price
     *
     * @return float
     */
    protected function calcSubTotal($qty, $price)
    {
        return intval($qty) * floatval($price);
    }

    /**
     * Re calculate sub total price
     *
     * @return void
     */
    protected function updateSubTotal()
    {
        $this->put('subtotal', $this->calcSubTotal($this->qty, $this->price));
    }
}
