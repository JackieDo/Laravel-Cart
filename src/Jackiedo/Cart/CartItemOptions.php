<?php namespace Jackiedo\Cart;

use Illuminate\Support\Collection;

/**
 * CartItemOtions
 *
 * @package JackieDo/Cart
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class CartItemOptions extends Collection
{

    /**
     * Get the option by the given key.
     *
     * @param string $key The option key.
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }
}
