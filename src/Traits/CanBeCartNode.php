<?php

namespace Jackiedo\Cart\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jackiedo\Cart\Cart;

/**
 * The CanBeCartNode traits.
 *
 * @package Jackiedo\Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
trait CanBeCartNode
{
    use BackToCreator { getCreator as protected; }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method     The method name
     * @param array  $parameters The input parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (strlen($method) > 3 && 'get' == substr($method, 0, 3)) {
            $attribute = Str::snake(substr($method, 3));

            if (array_key_exists($attribute, $this->attributes)) {
                return $this->attributes[$attribute];
            }
        }

        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }

    /**
     * Check if the parent node can be found.
     *
     * @return bool
     */
    public function hasKnownParentNode()
    {
        return $this->hasKnownCreator() && $this->getCreator()->hasKnownCreator();
    }

    /**
     * Get parent node instance that this instance is belong to.
     *
     * @return object
     */
    public function getParentNode()
    {
        return $this->getCreator()->getCreator();
    }

    /**
     * Get the cart instance that this node belong to.
     *
     * @return Jackiedo\Cart\Cart
     */
    public function getCart()
    {
        $parentNode = $this->getParentNode();

        if ($parentNode instanceof Cart) {
            return $parentNode;
        }

        return $parentNode->getCart();
    }

    /**
     * Get config of the cart instance thet this node belong to.
     *
     * @param null|string $name    The config name
     * @param mixed       $default The return value if the config does not exist
     *
     * @return mixed
     */
    public function getConfig($name = null, $default = null)
    {
        try {
            return $this->getCart()->getConfig($name, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Get the cart node's original attribute values.
     *
     * @param null|string $attribute The attribute
     * @param mixed       $default   The return value if attribute does not exist
     *
     * @return mixed
     */
    public function getOriginal($attribute = null, $default = null)
    {
        if ($attribute) {
            return Arr::get($this->attributes, $attribute, $default);
        }

        return $this->attributes;
    }

    /**
     * Dynamic attribute getter.
     *
     * @param null|string $attribute The attribute
     * @param mixed       $default   The return value if attribute does not exist
     *
     * @return mixed
     */
    public function get($attribute, $default = null)
    {
        if (!empty($attribute)) {
            $getMethod = Str::camel('get_' . $attribute);

            if (method_exists($this, $getMethod)) {
                $methodReflection       = new \ReflectionMethod($this, $getMethod);
                $isMethodPublic         = $methodReflection->isPublic();
                $numberOfRequiredParams = $methodReflection->getNumberOfRequiredParameters();

                if ($isMethodPublic && 0 == $numberOfRequiredParams) {
                    return $this->{$getMethod}();
                }
            }

            return $this->getOriginal($attribute, $default);
        }

        return $default;
    }

    /**
     * Get value of one or some extended informations of the current node
     * using "dot" notation.
     *
     * @param null|array|string $information The information want to get
     * @param mixed             $default
     *
     * @return mixed
     */
    public function getExtraInfo($information = null, $default = null)
    {
        $extraInfo = $this->attributes['extra_info'];

        if (is_null($information)) {
            return $extraInfo;
        }

        if (is_array($information)) {
            return Arr::only($extraInfo, $information);
        }

        return Arr::get($extraInfo, $information, $default);
    }

    /**
     * Set value for an attribute of this node.
     *
     * @param string $attribute The attribute want to set
     * @param mixed  $value     The value of attribute
     *
     * @return void
     */
    protected function setAttribute($attribute, $value)
    {
        if (!empty($attribute)) {
            $setter = Str::camel('set_' . $attribute);

            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            } else {
                $this->attributes[$attribute] = $value;
            }
        }
    }

    /**
     * Set value for the attributes of this node.
     *
     * @param array $attributes
     *
     * @return void
     */
    protected function setAttributes(array $attributes = [])
    {
        foreach ($attributes as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }

    /**
     * Set value for extended informations of the current node.
     * Can use "dot" notation with each information.
     *
     * @param array $informations
     *
     * @return void
     */
    protected function setExtraInfo(array $informations = [])
    {
        if (empty($informations)) {
            $this->attributes['extra_info'] = [];
        }

        foreach ($informations as $key => $value) {
            $key = trim($key, '.');

            if (!empty($key)) {
                Arr::set($this->attributes['extra_info'], $key, $value);
            }
        }
    }
}
