<?php

namespace Jackiedo\Cart;

use Closure;

/**
 * The ActionsContainer class
 * This is a container used to hold actions.
 *
 * @package JackieDo/Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class ActionsContainer extends Container
{
    /**
     * The name of the accepted class is the creator.
     *
     * @var array
     */
    protected $acceptedCreators = [
        Cart::class,
        Item::class,
    ];

    /**
     * Add an action into this container.
     *
     * @param array $attributes The action attributes
     * @param bool  $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Action
     */
    public function addAction(array $attributes = [], $withEvent = true)
    {
        $action = new Action($attributes);

        if ($withEvent) {
            $event  = $this->fireEvent('cart.action.applying', [$action]);

            if (false === $event) {
                return null;
            }
        }

        $actionHash = $action->getHash();

        if ($this->has($actionHash)) {
            // If the action is already exists in this container, we will update that action
            return $this->updateAction($actionHash, $attributes, $withEvent);
        }

        // If the action doesn't exist yet, put it to container
        $this->put($action->getHash(), $action);
        $this->sortActions();

        if ($withEvent) {
            $this->fireEvent('cart.action.applied', [$action]);
        }

        return $action;
    }

    /**
     * Update an action in actions container.
     *
     * @param string $actionHash The unique identifier of action
     * @param array  $attributes The new attributes
     * @param bool   $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Action
     */
    public function updateAction($actionHash, array $attributes = [], $withEvent = true)
    {
        $action = $this->getAction($actionHash);

        if ($withEvent) {
            $event  = $this->fireEvent('cart.action.updating', [&$attributes, $action]);

            if (false === $event) {
                return null;
            }
        }

        $action->update($attributes);

        $newHash = $action->getHash();

        if ($newHash != $actionHash) {
            $this->forget($actionHash);

            if ($this->has($newHash)) {
                $action = $this->updateAction($newHash, $attributes, $withEvent);
            } else {
                $this->put($newHash, $action);
                $this->sortActions();
            }
        }

        if ($withEvent) {
            $this->fireEvent('cart.action.updated', [$action]);
        }

        return $action;
    }

    /**
     * Get an action in this container by given hash.
     *
     * @param string $actionHash The unique identifier of action
     *
     * @return \Jackiedo\Cart\Action
     */
    public function getAction($actionHash)
    {
        if (!$this->has($actionHash)) {
            $this->throwInvalidHashException($actionHash);
        }

        return $this->get($actionHash);
    }

    /**
     * Get all actions in this container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return array
     */
    public function getActions($filter = null, $complyAll = true)
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
                $filtered = $this->filter(function ($action) use ($filter) {
                    return in_array($action->getHash(), $filter);
                });

                return $filtered->all();
            }

            // If filter is an associative
            if (!$complyAll) {
                $filtered = $this->filter(function ($action) use ($filter) {
                    $intersects = array_intersect_assoc_recursive($action->getFilterValues(), $filter);

                    return !empty($intersects);
                });
            } else {
                $filtered = $this->filter(function ($action) use ($filter) {
                    $diffs = array_diff_assoc_recursive($action->getFilterValues(), $filter);

                    return empty($diffs);
                });
            }

            return $filtered->all();
        }

        return [];
    }

    /**
     * Remove an action instance from this container.
     *
     * @param string $actionHash The unique identifier of the action instance
     * @param bool   $withEvent  Enable firing the event
     *
     * @return $this
     */
    public function removeAction($actionHash, $withEvent = true)
    {
        $action = $this->getAction($actionHash);

        if ($withEvent) {
            $event  = $this->fireEvent('cart.action.removing', [$action]);

            if (false === $event) {
                return $this;
            }
        }

        $cart = $action->getCart();
        $this->forget($actionHash);

        if ($withEvent) {
            $this->fireEvent('cart.action.removed', [$actionHash, clone $cart]);
        }

        return $this;
    }

    /**
     * Remove all action instances from this container.
     *
     * @param bool $withEvent Enable firing the event
     *
     * @return $this
     */
    public function clearActions($withEvent = true)
    {
        $cart = $this->getCreator();

        if ($cart instanceof Item) {
            $cart = $cart->getCart();
        }

        if ($withEvent) {
            $event = $this->fireEvent('cart.action.clearing', [$cart]);

            if (false === $event) {
                return $this;
            }
        }

        $this->forgetAll();

        if ($withEvent) {
            $this->fireEvent('cart.action.cleared', [$cart]);
        }

        return $this;
    }

    /**
     * Count all actions in this container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return int
     */
    public function countActions($filter = null, $complyAll = true)
    {
        if ($this->isEmpty()) {
            return 0;
        }

        return count($this->getActions($filter, $complyAll));
    }

    /**
     * Calculate the sum of action amounts in this container that match the given filter.
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

        $allActions = $this->getActions($filter, $complyAll);

        return array_reduce($allActions, function ($carry, $action) {
            return $carry + $action->getAmount();
        }, 0);
    }

    /**
     * Sort all actions using the orderId attribute.
     *
     * @return $this
     */
    protected function sortActions()
    {
        $sorted = $this->sortBy(function ($item) {
            return $item->getOrderId();
        });

        $this->items = $sorted->all();

        return $this;
    }

    /**
     * Sort all actions using the orderId attribute with descending direction.
     *
     * @return $this
     */
    protected function sortActionsDesc()
    {
        $sorted = $this->sortByDesc(function ($item) {
            return $item->getOrderId();
        });

        $this->items = $sorted->all();

        return $this;
    }
}
