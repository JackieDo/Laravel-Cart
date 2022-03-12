<?php

namespace Jackiedo\Cart;

use Illuminate\Support\Collection;
use Jackiedo\Cart\Exceptions\InvalidAssociatedException;
use Jackiedo\Cart\Exceptions\InvalidModelException;

/**
 * The Details class.
 *
 * @package JackieDo/Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class Details extends Collection
{
    /**
     * Dynamically access item from collection.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (class_exists('\Illuminate\Support\HigherOrderCollectionProxy') && in_array($key, static::$proxies)) {
            return new \Illuminate\Support\HigherOrderCollectionProxy($this, $key);
        }

        return $this->get($key);
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if (!array_key_exists($value, $this->items)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get an item from the collection by key.
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ('model' === $key) {
            if ($this->has(['id', 'associated_class'])) {
                $id              = $this->get('id');
                $associatedClass = $this->get('associated_class');

                if (!class_exists($associatedClass)) {
                    throw new InvalidAssociatedException('The [' . $associatedClass . '] class does not exist.');
                }

                $model = with(new $associatedClass)->findById($id);

                if (!$model) {
                    throw new InvalidModelException('The supplied associated model from [' . $associatedClass . '] does not exist.');
                }

                return $model;
            }

            return $default;
        }

        return parent::get($key, $default);
    }
}
