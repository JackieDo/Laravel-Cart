<?php

namespace Jackiedo\Cart\Traits;

/**
 * The CollectionForgetAll traits.
 *
 * @package Jackiedo\Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
trait CollectionForgetAll
{
    /**
     * Remove all items out of the collection.
     *
     * @return $this
     */
    public function forgetAll()
    {
        parent::__construct();

        return $this;
    }
}
