<?php namespace Jackiedo\Cart;

use Illuminate\Support\Collection;
use Jackiedo\Cart\Exceptions\CartInvalidItemException;
use Jackiedo\Cart\Exceptions\CartInvalidPriceException;
use Jackiedo\Cart\Exceptions\CartInvalidQtyException;

/**
 * The CartItem class
 *
 * @package JackieDo/Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class CartItem extends Collection
{

    /**
     * The Eloquent model that item is associated with.
     *
     * @var string
     */
    protected $model = null;

    /**
     * Create a cart item instance
     *
     * @param  mixed   $rawId       Unique ID of item before insert to the cart
     * @param  string  $title       Name of item
     * @param  int     $qty         Number of item
     * @param  float   $price       Unit price of one item
     * @param  array   $options     Array of additional options, such as 'size' or 'color'
     * @param  mixed   $associated  The model or the FQN of model that will be associated
     *
     * @return void;
     */
    public function __construct($rawId, $title, $qty, $price, array $options = [], $associated = null)
    {
        if (empty($rawId) || empty($title)) {
            throw new CartInvalidItemException('Invalid item raw id or item title argument.');
        }

        if (! is_numeric($qty) || $qty < 1) {
            throw new CartInvalidQtyException('Invalid item quantity argument.');
        }

        if (! is_numeric($price) || $price < 0) {
            throw new CartInvalidPriceException('Invalid item price argument.');
        }

        $attributes = [
            'id'         => $this->generateId($rawId, $associated, $options),
            'raw_id'     => $rawId,
            'title'      => $title,
            'qty'        => intval($qty),
            'price'      => floatval($price),
            'subtotal'   => $this->calculcateSubTotal($qty, $price),
            'options'    => new CartItemOptions($options),
            'associated' => $associated,
        ];

        parent::__construct($attributes);
    }

    /**
     * Magic accessor.
     *
     * @param  string  $property  Property name.
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ($property === 'model') {
            return with(new $this->associated)->find($this->raw_id);
        }

        if ($this->has($property)) {
            return $this->get($property);
        }

        return;
    }

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
     * Generate an unique id for the cart item
     *
     * @param  string  $rawId    Unique ID of item before insert to the cart
     * @param  array   $options  Array of additional options, such as 'size' or 'color'
     *
     * @return string
     */
    protected function generateId($rawId, $associated, $options = [])
    {
        ksort($options);
        return md5($rawId . serialize($associated) . serialize($options));
    }

    protected function updateId()
    {
        $this->put('id', $this->generateId($this->raw_id, $this->associate, $this->options->all()));
    }

    protected function calculcateSubTotal($qty, $price)
    {
        return intval($qty) * floatval($price);
    }

    protected function updateSubTotal()
    {
        $this->put('subtotal', $this->calculcateSubTotal($this->qty, $this->price));
    }
}
