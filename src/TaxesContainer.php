<?php

namespace Jackiedo\Cart;

use Closure;

/**
 * The TaxesContainer class
 * This is a container used to hold tax items.
 *
 * @package JackieDo/Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class TaxesContainer extends Container
{
    /**
     * The name of the accepted class is the creator.
     *
     * @var array
     */
    protected $acceptedCreators = [
        Cart::class,
    ];

    /**
     * Add a tax instance into this container.
     *
     * @param array $attributes The tax attributes
     * @param bool  $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Tax
     */
    public function addTax(array $attributes = [], $withEvent = true)
    {
        $tax = new Tax($attributes);

        if ($withEvent) {
            $event = $this->fireEvent('cart.tax.applying', [$tax]);

            if (false === $event) {
                return null;
            }
        }

        $taxHash = $tax->getHash();

        if ($this->has($taxHash)) {
            // If the tax is already exists in this container, we will update that tax
            return $this->updateTax($taxHash, $attributes, $withEvent);
        }

        // If the tax doesn't exist yet, put it to container
        $this->put($taxHash, $tax);

        if ($withEvent) {
            $this->fireEvent('cart.tax.applied', [$tax]);
        }

        return $tax;
    }

    /**
     * Update a tax in taxes container.
     *
     * @param string $taxHash    The unique identifier of tax
     * @param array  $attributes The new attributes
     * @param bool   $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Tax
     */
    public function updateTax($taxHash, array $attributes = [], $withEvent = true)
    {
        $tax = $this->getTax($taxHash);

        if ($withEvent) {
            $event = $this->fireEvent('cart.tax.updating', [&$attributes, $tax]);

            if (false === $event) {
                return null;
            }
        }

        $tax->update($attributes);

        $newHash = $tax->getHash();

        if ($newHash != $taxHash) {
            $this->forget($taxHash);
            $this->put($newHash, $tax);
        }

        if ($withEvent) {
            $this->fireEvent('cart.tax.updated', [$tax]);
        }

        return $tax;
    }

    /**
     * Get a tax instance in this container by given hash.
     *
     * @param string $taxHash The unique identifier of tax instance
     *
     * @return \Jackiedo\Cart\Tax
     */
    public function getTax($taxHash)
    {
        if (!$this->has($taxHash)) {
            $this->throwInvalidHashException($taxHash);
        }

        return $this->get($taxHash);
    }

    /**
     * Get all tax instance in this container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return array
     */
    public function getTaxes($filter = null, $complyAll = true)
    {
        // If there is no filter, return all taxes
        if (is_null($filter)) {
            return $this->all();
        }

        // If filter is a closure
        if ($filter instanceof \Closure) {
            return $this->filter($filter)->all();
        }

        // If filter is an array
        if (is_array($filter)) {
            // If filter is not an associative array
            if (!isAssocArray($filter)) {
                $filtered = $this->filter(function ($tax) use ($filter) {
                    return in_array($tax->getHash(), $filter);
                });

                return $filtered->all();
            }

            // If filter is an associative
            if (!$complyAll) {
                $filtered = $this->filter(function ($tax) use ($filter) {
                    $intersects = array_intersect_assoc_recursive($tax->getFilterValues(), $filter);

                    return !empty($intersects);
                });
            } else {
                $filtered = $this->filter(function ($tax) use ($filter) {
                    $diffs = array_diff_assoc_recursive($tax->getFilterValues(), $filter);

                    return empty($diffs);
                });
            }

            return $filtered->all();
        }

        return [];
    }

    /**
     * Remove a tax instance from this container.
     *
     * @param string $taxHash   The unique identifier of the tax instance
     * @param bool   $withEvent Enable firing the event
     *
     * @return $this
     */
    public function removeTax($taxHash, $withEvent = true)
    {
        $tax = $this->getTax($taxHash);

        if ($withEvent) {
            $event = $this->fireEvent('cart.tax.removing', [$tax]);

            if (false === $event) {
                return $this;
            }
        }

        $cart = $tax->getCart();
        $this->forget($taxHash);

        if ($withEvent) {
            $this->fireEvent('cart.tax.removed', [$taxHash, clone $cart]);
        }

        return $this;
    }

    /**
     * Remove all tax instances from this container.
     *
     * @param bool $withEvent Enable firing the event
     *
     * @return $this
     */
    public function clearTaxes($withEvent = true)
    {
        $cart = $this->getCreator();

        if ($withEvent) {
            $event = $this->fireEvent('cart.tax.clearing', [$cart]);

            if (false === $event) {
                return $this;
            }
        }

        $this->forgetAll();

        if ($withEvent) {
            $this->fireEvent('cart.tax.cleared', [$cart]);
        }

        return $this;
    }

    /**
     * Count all tax instances in this container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return int
     */
    public function countTaxes($filter = null, $complyAll = true)
    {
        if ($this->isEmpty()) {
            return 0;
        }

        return count($this->getTaxes($filter, $complyAll));
    }

    /**
     * Get the sum of tax rate for all tax instances in this container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return float
     */
    public function sumRate($filter = null, $complyAll = true)
    {
        if ($this->isEmpty()) {
            return 0;
        }

        $allTaxes = $this->getTaxes($filter, $complyAll);

        return array_reduce($allTaxes, function ($carry, $tax) {
            return $carry + $tax->getRate();
        }, 0);
    }

    /**
     * Get the sum of tax amount for all tax instances in this container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return float
     */
    public function sumAmount($filter = null, $complyAll = true)
    {
        if ($this->isEmpty()) {
            return 0;
        }

        $allTaxes = $this->getTaxes($filter, $complyAll);

        return array_reduce($allTaxes, function ($carry, $tax) {
            return $carry + $tax->getAmount();
        }, 0);
    }
}
