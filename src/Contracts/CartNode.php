<?php

namespace Jackiedo\Cart\Contracts;

/**
 * The CartNode interface.
 *
 * @package Jackiedo\Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
interface CartNode
{
    /**
     * Check if the parent node can be found.
     *
     * @return bool
     */
    public function hasKnownParentNode();

    /**
     * Get parent node instance that this instance is belong to.
     *
     * @return object
     */
    public function getParentNode();

    /**
     * Get the cart instance that this node belong to.
     *
     * @return \Jackiedo\Cart\Cart
     */
    public function getCart();

    /**
     * Determines which values ​​to filter.
     *
     * @return array
     */
    public function getFilterValues();

    /**
     * Get config of the cart instance thet this node belong to.
     *
     * @param null|string $name    The config name
     * @param mixed       $default The return value if the config does not exist
     *
     * @return mixed
     */
    public function getConfig($name = null, $default = null);

    /**
     * Get the cart node's original attribute values.
     *
     * @param null|string $attribute The attribute
     * @param mixed       $default   The return value if attribute does not exist
     *
     * @return mixed
     */
    public function getOriginal($attribute = null, $default = null);

    /**
     * Dynamic attribute getter.
     *
     * @param string $attribute The attribute
     * @param mixed  $default   The return value if attribute does not exist
     *
     * @return mixed
     */
    public function get($attribute, $default = null);
}
