<?php namespace Jackiedo\Cart;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * CartItem
 *
 * Adapted from https://github.com/Crinsane/LaravelShoppingcart
 *
 * @package JackieDo/Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class CartItem extends Collection
{

    /**
     * The Eloquent model a cart is associated with.
     *
     * @var string
     */
    protected $model;

    /**
     * Magic accessor.
     *
     * @param  string  $property  Property name.
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ($this->has($property)) {
            return $this->get($property);
        }

        if (!$this->get('associated')) {
            return;
        }

        $model = $this->get('associated');
        $class = explode('\\', $model);

        if (Str::snake(end($class)) == $property) {
            $model = new $model();

            return $model->find($this->id);
        }

        return;
    }

    /**
     * Return the raw ID of item.
     *
     * @return string
     */
    public function rawId()
    {
        return $this->rawId;
    }

    public function search($search, $strict = false)
    {
        $found = false;
        foreach ($search as $key => $value) {
            if ($key === 'options') {
                $found = $this->{$key}->search($value);
            } else {
                $found = ($this->{$key} == $value) ? true : false;
            }

            if ($found) {
                return true;
            }
        }

        return $found;
    }
}
