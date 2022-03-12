<?php

namespace Jackiedo\Cart\Traits;

/**
 * The CanApplyAction traits.
 *
 * @package Jackiedo\Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
trait CanApplyAction
{
    /**
     * Add an action into the actions container.
     *
     * @param array $attributes The action attributes
     * @param bool  $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Action
     */
    public function applyAction(array $attributes = [], $withEvent = true)
    {
        if (!$this->canApplyAction()) {
            return null;
        }

        return $this->getActionsContainer()->addAction($attributes, $withEvent);
    }

    /**
     * Update an action in the actions container.
     *
     * @param string $actionHash The unique identifier of the action
     * @param array  $attributes The new attributes
     * @param bool   $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Action
     */
    public function updateAction($actionHash, array $attributes = [], $withEvent = true)
    {
        return $this->getActionsContainer()->updateAction($actionHash, $attributes, $withEvent);
    }

    /**
     * Determines if the action exists in the actions container by given action hash.
     *
     * @param string $actionHash The unique identifier of the action
     *
     * @return bool
     */
    public function hasAction($actionHash)
    {
        return $this->getActionsContainer()->has($actionHash);
    }

    /**
     * Get an action in the actions container.
     *
     * @param string $actionHash The unique identifier of the action
     *
     * @return \Jackiedo\Cart\Action
     */
    public function getAction($actionHash)
    {
        return $this->getActionsContainer()->getAction($actionHash);
    }

    /**
     * Get all actions in the actions container that match the given filter.
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
        return $this->getActionsContainer()->getActions($filter, $complyAll);
    }

    /**
     * Remove an action from the actions container.
     *
     * @param string $actionHash The unique identifier of the action
     * @param bool   $withEvent  Enable firing the event
     *
     * @return $this
     */
    public function removeAction($actionHash, $withEvent = true)
    {
        $this->getActionsContainer()->removeAction($actionHash, $withEvent);

        return $this;
    }

    /**
     * Remove all actions from the actions container.
     *
     * @param bool $withEvent Enable firing the event
     *
     * @return $this
     */
    public function clearActions($withEvent = true)
    {
        $this->getActionsContainer()->clearActions($withEvent);

        return $this;
    }

    /**
     * Count all actions in the actions container that match the given filter.
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
        return $this->getActionsContainer()->countActions($filter, $complyAll);
    }

    /**
     * Calculate the sum of action amounts in the actions container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return float
     */
    public function sumActionsAmount($filter = null, $complyAll = true)
    {
        return $this->getActionsContainer()->sumAmount($filter, $complyAll);
    }
}
