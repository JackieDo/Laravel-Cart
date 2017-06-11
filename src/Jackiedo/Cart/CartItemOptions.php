<?php namespace Jackiedo\Cart;

use Illuminate\Support\Collection;

/**
 * CartItemOtions
 *
 * Adapted from https://github.com/Crinsane/LaravelShoppingcart
 *
 * @package JackieDo/Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class CartItemOptions extends Collection
{

    /**
     * Magic accessor.
     *
     * @param string $property Property name.
     *
     * @return mixed
     */
    public function __get($arg)
    {
        if ($this->has($arg)) {
            return $this->get($arg);
        }

        return null;
    }

    public function search($search, $strict = false)
    {
        if ($this->intersect($search)->isEmpty()) {
            return false;
        }

        return true;
    }
}
