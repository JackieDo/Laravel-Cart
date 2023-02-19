<?php

namespace Jackiedo\Cart;

use Illuminate\Support\Collection;
use Jackiedo\Cart\Exceptions\InvalidHashException;
use Jackiedo\Cart\Traits\BackToCreator;
use Jackiedo\Cart\Traits\CollectionForgetAll;
use Jackiedo\Cart\Traits\FireEvent;

/**
 * The Container class.
 *
 * @package JackieDo/Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class Container extends Collection
{
    use CollectionForgetAll;
    use BackToCreator;
    use FireEvent;

    /**
     * Create a new container.
     *
     * @param mixed $items
     *
     * @return void
     */
    public function __construct($items = [])
    {
        $this->storeCreator();

        parent::__construct($items);
    }

    /**
     * Get details information of this container as a collection.
     *
     * @return \Jackiedo\Cart\Details
     */
    public function getDetails()
    {
        $details    = new Details;
        $allActions = $this->all();

        foreach ($allActions as $key => $value) {
            $details->put($key, $value->getDetails());
        }

        return $details;
    }

    /**
     * Check for the existence of the hash string.
     *
     * @param string $hash The hash string
     *
     * @return void
     *
     * @throws \Jackiedo\Cart\Exceptions\InvalidHashException
     */
    protected function throwInvalidHashException($hash)
    {
        throw new InvalidHashException('Could not find any action with hash ' . $hash . ' in the actions container.');
    }
}
