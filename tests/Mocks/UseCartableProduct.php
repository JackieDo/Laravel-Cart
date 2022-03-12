<?php

use Jackiedo\Cart\Contracts\UseCartable;
use Jackiedo\Cart\Traits\CanUseCart;

/**
 * This is a sample model use to associated with the cart item.
 */
class UseCartableProduct implements UseCartable
{
    use CanUseCart;

    /**
     * The identifier of model.
     *
     * @var int
     */
    public $id = 1;

    /**
     * The name of model.
     *
     * @var string
     */
    public $name = 'Polo T-shirt for men';

    /**
     * The price of model.
     *
     * @var float
     */
    public $unit_price = 100;

    /**
     * Get the identifier of the UseCartable item.
     *
     * @return int|string
     */
    public function getUseCartableId()
    {
        return $this->id;
    }

    /**
     * Get the title of the UseCartable item.
     *
     * @return string
     */
    public function getUseCartableTitle()
    {
        return $this->name;
    }

    /**
     * Get the price of the UseCartable item.
     *
     * @return float
     */
    public function getUseCartablePrice()
    {
        return $this->unit_price;
    }

    /**
     * Find a model by its identifier.
     *
     * @param int $id The identifier of model
     *
     * @return null|\Illuminate\Support\Collection|static
     */
    public function findById($id)
    {
        return $this;
    }
}
