<?php

namespace Jackiedo\Cart;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Jackiedo\Cart\Exceptions\InvalidCartNameException;
use Jackiedo\Cart\Traits\CanApplyAction;
use Jackiedo\Cart\Traits\FireEvent;

/**
 * The Cart class.
 *
 * Used to manage the cart data in session.
 *
 * @package Jackiedo\Cart
 *
 * @author  Jackie Do <anhvudo@gmail.com>
 */
class Cart
{
    use CanApplyAction;
    use FireEvent;

    /**
     * The root session name.
     *
     * @var string
     */
    protected $rootSessionName;

    /**
     * The default cart name.
     *
     * @var string
     */
    protected $defaultCartName = 'default';

    /**
     * The name of current cart instance.
     *
     * @var string
     */
    protected $cartName;

    /**
     * Create cart instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->rootSessionName = '_' . md5(config('app.name') . __NAMESPACE__);
        $defaultCartName       = config('cart.default_cart_name');

        if (is_string($defaultCartName) && !empty($defaultCartName)) {
            $this->defaultCartName = $defaultCartName;
        }

        $this->name();
    }

    /**
     * Select a cart to work with.
     *
     * @param null|string $name The cart name
     *
     * @return $this
     */
    public function name($name = null)
    {
        $this->cartName = $this->rootSessionName . '.' . $this->standardizeCartName($name);

        $this->initSessions();

        return $this;
    }

    /**
     * Create an another cart instance with the specific name.
     *
     * @param null|string $name The cart name
     *
     * @return $this
     */
    public function newInstance($name = null)
    {
        $name = $this->standardizeCartName($name);

        if ($name === $this->getName()) {
            return clone $this;
        }

        $newInstance = new static;

        $newInstance->name($name);

        return $newInstance;
    }

    /**
     * Determines whether this cart has been grouped.
     *
     * @return bool
     */
    public function hasBeenGrouped()
    {
        return Str::contains($this->getName(), ['.']);
    }

    /**
     * Determines whether this cart is in the specific group.
     *
     * @param string $groupName The specific group name
     *
     * @return bool
     */
    public function isInGroup($groupName)
    {
        if (is_null($groupName)) {
            return false;
        }

        $currentGroupName = $this->getGroupName();

        if (is_null($currentGroupName)) {
            return false;
        }

        return Str::startsWith($currentGroupName, $groupName);
    }

    /**
     * Get the group name of the cart.
     *
     * @return string
     */
    public function getGroupName()
    {
        if (!$this->hasBeenGrouped()) {
            return null;
        }

        $splitParts = explode('.', $this->getName());
        array_pop($splitParts);

        return implode('.', $splitParts);
    }

    /**
     * Get the current cart name.
     *
     * @return string
     */
    public function getName()
    {
        return substr($this->cartName, strlen($this->rootSessionName) + 1);
    }

    /**
     * Get config of this cart.
     *
     * @param string $name    The config name
     * @param mixed  $default The return value if the config does not exist
     *
     * @return mixed
     */
    public function getConfig($name = null, $default = null)
    {
        if ($name) {
            return session($this->getSessionPath('config.' . $name), $default);
        }

        return session($this->getSessionPath('config'), $default);
    }

    /**
     * Change whether the cart status is used for commercial or not.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function useForCommercial($status = true)
    {
        if ($this->isEmpty()) {
            $status = (bool) $status;

            $this->setConfig('use_for_commercial', $status);

            if ($status) {
                session()->put($this->getSessionPath('applied_actions'), new ActionsContainer);

                if ($this->getConfig('use_builtin_tax')) {
                    session()->put($this->getSessionPath('applied_taxes'), new TaxesContainer);
                } else {
                    session()->forget($this->getSessionPath('applied_taxes'));
                }
            } else {
                session()->forget($this->getSessionPath('applied_actions'));
                session()->forget($this->getSessionPath('applied_taxes'));
            }
        }

        return $this;
    }

    /**
     * Enable or disable built-in tax system for the cart.
     * This is only possible if the cart is empty.
     *
     * @param bool $status
     *
     * @return $this
     */
    public function useBuiltinTax($status = true)
    {
        if ($this->isEmpty()) {
            $status = (bool) $status;

            $this->setConfig('use_builtin_tax', $status);

            if ($status && $this->getConfig('use_for_commercial', false)) {
                session()->put($this->getSessionPath('applied_taxes'), new TaxesContainer);
            } else {
                session()->forget($this->getSessionPath('applied_taxes'));
            }
        }

        return $this;
    }

    /**
     * Set default action rules for the cart.
     * This is only possible if the cart is empty.
     *
     * @param array $rules The default action rules
     */
    public function setDefaultActionRules(array $rules = [])
    {
        if ($this->isEmpty()) {
            $this->setConfig('default_action_rules', $rules);
        }

        return $this;
    }

    /**
     * Set action groups order for the cart.
     *
     * @param array $order The action groups order
     *
     * @return $this
     */
    public function setActionGroupsOrder(array $order = [])
    {
        $this->setConfig('action_groups_order', $order);

        return $this;
    }

    /**
     * Determines if the cart is empty.
     *
     * @return bool returns true if the cart has no items, no taxes,
     *              and no action has been applied yet
     */
    public function isEmpty()
    {
        return $this->hasNoItems() && $this->hasNoActions() && $this->hasNoTaxes();
    }

    /**
     * Determines if the cart has no items.
     *
     * @return bool
     */
    public function hasNoItems()
    {
        return $this->getItemsContainer()->isEmpty();
    }

    /**
     * Determines if the cart has no actions.
     *
     * @return bool
     */
    public function hasNoActions()
    {
        return $this->getActionsContainer()->isEmpty();
    }

    /**
     * Determines if the cart has no taxes.
     *
     * @return bool
     */
    public function hasNoTaxes()
    {
        return $this->getTaxesContainer()->isEmpty();
    }

    /**
     * Determines if current cart is used for commcercial.
     *
     * @return bool
     */
    public function isCommercialCart()
    {
        return $this->getConfig('use_for_commercial', false);
    }

    /**
     * Determines if current cart is enabled built-in tax system.
     *
     * @return bool
     */
    public function isEnabledBuiltinTax()
    {
        if (!$this->getConfig('use_for_commercial', false)) {
            return false;
        }

        return $this->getConfig('use_builtin_tax', false);
    }

    /**
     * Remove cart from session.
     *
     * @param bool $withEvent Enable firing the event
     *
     * @return bool
     */
    public function destroy($withEvent = true)
    {
        if ($withEvent) {
            $eventResponse = $this->fireEvent('cart.destroying', clone $this);

            if (false === $eventResponse) {
                return false;
            }
        }

        session()->forget($this->getSessionPath());

        if ($withEvent) {
            $this->fireEvent('cart.destroyed');
        }

        return true;
    }

    /**
     * Add an item into the items container.
     *
     * @param array $attributes The item attributes
     * @param bool  $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Item
     */
    public function addItem(array $attributes = [], $withEvent = true)
    {
        return $this->getItemsContainer()->addItem($attributes, $withEvent);
    }

    /**
     * Update an item in the items container.
     *
     * @param string    $itemHash   The unique identifier of the item
     * @param array|int $attributes New quantity of item or array of new attributes to update
     * @param bool      $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Item
     */
    public function updateItem($itemHash, $attributes = [], $withEvent = true)
    {
        return $this->getItemsContainer()->updateItem($itemHash, $attributes, $withEvent);
    }

    /**
     * Remove an item out of the items container.
     *
     * @param string $itemHash  The unique identifier of the item
     * @param bool   $withEvent Enable firing the event
     *
     * @return $this
     */
    public function removeItem($itemHash, $withEvent = true)
    {
        $this->getItemsContainer()->removeItem($itemHash, $withEvent);

        return $this;
    }

    /**
     * Delete all items in the items container.
     *
     * @param bool $withEvent Enable firing the event
     *
     * @return $this
     */
    public function clearItems($withEvent = true)
    {
        $this->getItemsContainer()->clearItems($withEvent);

        return $this;
    }

    /**
     * Get an item in the items container.
     *
     * @param string $itemHash The unique identifier of the item
     *
     * @return \Jackiedo\Cart\Item
     */
    public function getItem($itemHash)
    {
        return $this->getItemsContainer()->getItem($itemHash);
    }

    /**
     * Get all items in the items container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return array
     */
    public function getItems($filter = null, $complyAll = true)
    {
        return $this->getItemsContainer()->getItems($filter, $complyAll);
    }

    /**
     * Count the number of items in the items container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return int
     */
    public function countItems($filter = null, $complyAll = true)
    {
        return $this->getItemsContainer()->countItems($filter, $complyAll);
    }

    /**
     * Sum the quantities of all items in the items container that match the given filter.
     *
     * @param mixed $filter    Search filter
     * @param bool  $complyAll indicates that the results returned must satisfy
     *                         all the conditions of the filter at the same time
     *                         or that only parts of the filter
     *
     * @return int
     */
    public function sumItemsQuantity($filter = null, $complyAll = true)
    {
        return $this->getItemsContainer()->sumQuantity($filter, $complyAll);
    }

    /**
     * Determines if the item exists in the items container.
     *
     * @param string $itemHash The unique identifier of the item
     *
     * @return bool
     */
    public function hasItem($itemHash)
    {
        return $this->getItemsContainer()->has($itemHash);
    }

    /**
     * Set value for one or some extended informations of the cart.
     *
     * @param array|string $information The information want to set
     * @param mixed        $value       The value of information
     *
     * @return $this
     */
    public function setExtraInfo($information, $value = null)
    {
        return $this->setGroupExtraInfo($this->getName(), $information, $value);
    }

    /**
     * Get value of one or some extended informations of the cart
     * using "dot" notation.
     *
     * @param null|array|string $information The information want to get
     * @param mixed             $default     The return value if information does not exist
     *
     * @return mixed
     */
    public function getExtraInfo($information = null, $default = null)
    {
        return $this->getGroupExtraInfo($this->getName(), $information, $default);
    }

    /**
     * Remove one or some extended informations of the cart
     * using "dot" notation.
     *
     * @param null|array|string $information The information want to remove
     *
     * @return $this
     */
    public function removeExtraInfo($information = null)
    {
        return $this->removeGroupExtraInfo($this->getName(), $information);
    }

    /**
     * Set value for one or some extended informations of the group
     * using "dot" notation.
     *
     * @param string       $groupName   The name of the cart group
     * @param array|string $information The information want to set
     * @param mixed        $value       The value of information
     *
     * @return $this
     */
    public function setGroupExtraInfo($groupName, $information, $value = null)
    {
        $groupName = trim($groupName, '.');

        if ($groupName) {
            if (!is_array($information)) {
                $information = [
                    $information => $value,
                ];
            }

            foreach ($information as $key => $value) {
                $key = trim($key, '.');

                if (!empty($key)) {
                    session()->put($this->rootSessionName . '.' . $groupName . '.extra_info.' . $key, $value);
                }
            }
        }

        return $this;
    }

    /**
     * Get value of one or some extended informations of the group
     * using "dot" notation.
     *
     * @param string            $groupName   The name of the cart group
     * @param null|array|string $information The information want to get
     * @param mixed             $default     The return value if information does not exist
     *
     * @return mixed
     */
    public function getGroupExtraInfo($groupName, $information = null, $default = null)
    {
        $groupName = trim($groupName, '.');

        if ($groupName) {
            $extraInfo = session($this->rootSessionName . '.' . $groupName . '.extra_info', []);

            if (is_null($information)) {
                return $extraInfo;
            }

            if (is_array($information)) {
                return Arr::only($extraInfo, $information);
            }

            return Arr::get($extraInfo, $information, $default);
        }

        return $default;
    }

    /**
     * Remove one or some extended informations of the group
     * using "dot" notation.
     *
     * @param string            $groupName   The name of the cart group
     * @param null|array|string $information The information want to remove
     *
     * @return $this
     */
    public function removeGroupExtraInfo($groupName, $information = null)
    {
        $groupName = trim($groupName, '.');

        if ($groupName) {
            if (is_null($information)) {
                session()->put($this->rootSessionName . '.' . $groupName . '.extra_info', []);

                return $this;
            }

            $informations = (array) $information;

            foreach ($informations as $key) {
                $key = trim($key, '.');

                if (!empty($key)) {
                    session()->forget($this->rootSessionName . '.' . $groupName . '.extra_info.' . $key);
                }
            }
        }

        return $this;
    }

    /**
     * Add a tax into the taxes container of this cart.
     *
     * @param array $attributes The tax attributes
     * @param bool  $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Tax
     */
    public function applyTax(array $attributes = [], $withEvent = true)
    {
        if (!$this->isEnabledBuiltinTax()) {
            return null;
        }

        return $this->getTaxesContainer()->addTax($attributes, $withEvent);
    }

    /**
     * Update a tax in the taxes container.
     *
     * @param string $taxHash    The unique identifire of the tax instance
     * @param array  $attributes The new attributes
     * @param bool   $withEvent  Enable firing the event
     *
     * @return null|\Jackiedo\Cart\Tax
     */
    public function updateTax($taxHash, array $attributes = [], $withEvent = true)
    {
        return $this->getTaxesContainer()->updateTax($taxHash, $attributes, $withEvent);
    }

    /**
     * Get an applied tax in the taxes container of this cart.
     *
     * @param string $taxHash The unique identifire of the tax instance
     *
     * @return \Jackiedo\Cart\Tax
     */
    public function getTax($taxHash)
    {
        return $this->getTaxesContainer()->getTax($taxHash);
    }

    /**
     * Get all tax instances in the taxes container of this cart that match the given filter.
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
        return $this->getTaxesContainer()->getTaxes($filter, $complyAll);
    }

    /**
     * Count all taxes in the actions container that match the given filter.
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
        return $this->getTaxesContainer()->countTaxes($filter, $complyAll);
    }

    /**
     * Determines if the tax exists in the taxes container of this cart.
     *
     * @param string $taxHash The unique identifier of the tax
     *
     * @return bool
     */
    public function hasTax($taxHash)
    {
        return $this->getTaxesContainer()->has($taxHash);
    }

    /**
     * Remove an applied tax from the taxes container of this cart.
     *
     * @param string $taxHash   The unique identifier of the tax instance
     * @param bool   $withEvent Enable firing the event
     *
     * @return $this
     */
    public function removeTax($taxHash, $withEvent = true)
    {
        $this->getTaxesContainer()->removeTax($taxHash, $withEvent);

        return $this;
    }

    /**
     * Remove all apllied taxes from the taxes container of this cart.
     *
     * @param bool $withEvent Enable firing the event
     *
     * @return $this
     */
    public function clearTaxes($withEvent = true)
    {
        $this->getTaxesContainer()->clearTaxes($withEvent);

        return $this;
    }

    /**
     * Get the subtotal amount of all items in the items container.
     *
     * @return float
     */
    public function getItemsSubtotal()
    {
        return $this->getItemsContainer()->sumSubtotal();
    }

    /**
     * Get the sum amount of all items subtotal amount and all actions amount.
     *
     * @return float
     */
    public function getSubtotal()
    {
        $enabledActionsAmount = $this->getActionsContainer()->sumAmount();

        return $this->getItemsSubtotal() + $enabledActionsAmount;
    }

    /**
     * Calculate total taxable amounts include the taxable amount of cart and all items.
     *
     * @return float
     */
    public function getTaxableAmount()
    {
        if (!$this->isEnabledBuiltinTax()) {
            return 0;
        }

        $itemsTaxableAmount = $this->getItemsContainer()->sumTaxableAmount();
        $cartTaxableAmount  = $this->getActionsContainer()->sumAmount([
            'rules' => [
                'taxable' => true,
            ],
        ]);

        return $itemsTaxableAmount + $cartTaxableAmount;
    }

    /**
     * Get the total tax rate applied to the current cart.
     *
     * @return float
     */
    public function getTaxRate()
    {
        if (!$this->isEnabledBuiltinTax()) {
            return 0;
        }

        return $this->getTaxesContainer()->sumRate();
    }

    /**
     * Get the total tax amount applied to the current cart.
     *
     * @return float
     */
    public function getTaxAmount()
    {
        if (!$this->isEnabledBuiltinTax()) {
            return 0;
        }

        return $this->getTaxesContainer()->sumAmount();
    }

    /**
     * Get the total amount of the current cart.
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->getSubtotal() + $this->getTaxAmount();
    }

    /**
     * Get all information of cart as a collection.
     *
     * @param bool $withItems   Include details of added items in the result
     * @param bool $withActions Include details of applied actions in the result
     * @param bool $withTaxes   Include details of applied taxes in the result
     *
     * @return \Jackiedo\Cart\Details
     */
    public function getDetails($withItems = true, $withActions = true, $withTaxes = true)
    {
        $details           = new Details;
        $isCommercialCart  = $this->isCommercialCart();
        $enabledBuiltinTax = $this->isEnabledBuiltinTax();
        $itemsContainer    = $this->getItemsContainer();

        $details->put('type', 'cart');
        $details->put('name', $this->getName());
        $details->put('commercial_cart', $isCommercialCart);
        $details->put('enabled_builtin_tax', $enabledBuiltinTax);
        $details->put('items_count', $this->countItems());
        $details->put('quantities_sum', $this->sumItemsQuantity());

        if ($isCommercialCart) {
            $actionsContainer = $this->getActionsContainer();

            $details->put('items_subtotal', $this->getItemsSubtotal());
            $details->put('actions_count', $this->countActions());
            $details->put('actions_amount', $this->sumActionsAmount());

            if ($enabledBuiltinTax) {
                $taxesContainer = $this->getTaxesContainer();

                $details->put('subtotal', $this->getSubtotal());
                $details->put('taxes_count', $this->countTaxes());
                $details->put('taxable_amount', $this->getTaxableAmount());
                $details->put('tax_rate', $this->getTaxRate());
                $details->put('tax_amount', $this->getTaxAmount());
                $details->put('total', $this->getTotal());

                if ($withItems) {
                    $details->put('items', $itemsContainer->getDetails($withActions));
                }

                if ($withActions) {
                    $details->put('applied_actions', $actionsContainer->getDetails());
                }

                if ($withTaxes) {
                    $details->put('applied_taxes', $taxesContainer->getDetails());
                }
            } else {
                $details->put('total', $this->getSubtotal());

                if ($withItems) {
                    $details->put('items', $itemsContainer->getDetails($withActions));
                }

                if ($withActions) {
                    $details->put('applied_actions', $actionsContainer->getDetails());
                }
            }
        } else {
            if ($withItems) {
                $details->put('items', $itemsContainer->getDetails($withActions));
            }
        }

        $details->put('extra_info', new Details($this->getExtraInfo(null, [])));

        return $details;
    }

    /**
     * Get all information of cart group as a collection.
     *
     * @param null|string $groupName            The group part from cart name
     * @param bool        $withCartsHaveNoItems Include carts have no items in the result
     * @param bool        $withItems            Include details of added items in the result
     * @param bool        $withActions          Include details of applied actions in the result
     * @param bool        $withTaxes            Include details of applied taxes in the result
     *
     * @return \Jackiedo\Cart\Details
     */
    public function getGroupDetails($groupName = null, $withCartsHaveNoItems = false, $withItems = true, $withActions = true, $withTaxes = true)
    {
        $groupName = $groupName ?: $this->getGroupName();

        return $this->groupAnalysic($groupName, $withCartsHaveNoItems, $withItems, $withActions, $withTaxes);
    }

    /**
     * Standardize the cart name.
     *
     * @param null|string $name The cart name before standardized
     *
     * @return string
     *
     * @throws \Jackiedo\Cart\Exceptions\InvalidCartNameException
     */
    protected function standardizeCartName($name = null)
    {
        $name = $name ?: $this->defaultCartName;
        $name = trim($name, '.');

        if (in_array('extra_info', explode('.', $name))) {
            throw new InvalidCartNameException("The keyword 'extra_info' is not allowed to name the cart or group.");
        }

        return $name;
    }

    /**
     * Initialize attributes for current cart instance.
     *
     * @return bool return false if attributes already exist without initialization
     */
    protected function initSessions()
    {
        if (!session()->has($this->getSessionPath())) {
            $appConfig           = config('cart');
            $noneCommercialCarts = array_values((array) Arr::get($appConfig, 'none_commercial_carts', []));
            $useForCommercial    = !in_array($this->getName(), $noneCommercialCarts);
            $useBuiltinTax       = (bool) Arr::get($appConfig, 'use_builtin_tax', false);

            $this->setConfig('use_for_commercial', $useForCommercial);
            $this->setConfig('use_builtin_tax', $useBuiltinTax);
            $this->setConfig('default_tax_rate', floatval(Arr::get($appConfig, 'default_tax_rate', 0)));
            $this->setConfig('default_action_rules', (array) Arr::get($appConfig, 'default_action_rules', []));
            $this->setConfig('action_groups_order', array_values((array) Arr::get($appConfig, 'action_groups_order', [])));

            session()->put($this->getSessionPath('type'), 'cart');
            session()->put($this->getSessionPath('name'), $this->getName());
            session()->put($this->getSessionPath('extra_info'), []);
            session()->put($this->getSessionPath('items'), new ItemsContainer);

            if ($useForCommercial) {
                session()->put($this->getSessionPath('applied_actions'), new ActionsContainer);

                if ($useBuiltinTax) {
                    session()->put($this->getSessionPath('applied_taxes'), new TaxesContainer);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Set config for this cart.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    protected function setConfig($name, $value = null)
    {
        if ($name) {
            session()->put($this->getSessionPath('config.' . $name), $value);
        }

        return $this;
    }

    /**
     * Get the session path from the path to the cart.
     *
     * @param mixed $sessionKey
     *
     * @return string $sessionKey The sub session key from session of this cart
     */
    protected function getSessionPath($sessionKey = null)
    {
        if (is_null($sessionKey)) {
            return $this->cartName;
        }

        return $this->cartName . '.' . $sessionKey;
    }

    /**
     * Get the items container.
     *
     * @return \Jackiedo\Cart\ItemsContainer
     */
    protected function getItemsContainer()
    {
        return session($this->getSessionPath('items'), new ItemsContainer);
    }

    /**
     * Get the taxes container.
     *
     * @return \Jackiedo\Cart\TaxesContainer
     */
    protected function getTaxesContainer()
    {
        return session($this->getSessionPath('applied_taxes'), new TaxesContainer);
    }

    /**
     * Get the actions container.
     *
     * @return \Jackiedo\Cart\ActionsContainer
     */
    protected function getActionsContainer()
    {
        return session($this->getSessionPath('applied_actions'), new ActionsContainer);
    }

    /**
     * Indicates whether this instance can apply cart.
     *
     * @return bool
     */
    protected function canApplyAction()
    {
        if ($this->isCommercialCart()) {
            return true;
        }

        return false;
    }

    /**
     * Analyze data from the session group.
     *
     * @param string $groupName            The group part from cart name
     * @param bool   $withCartsHaveNoItems Include carts have no items in the result
     * @param bool   $withItems            Include details of added items in the result
     * @param bool   $withActions          Include details of applied actions in the result
     * @param bool   $withTaxes            Include details of applied taxes in the result
     * @param array  $moneyAmount          Information on cumulative amounts from the details of the subsections
     * @param array  $moneyAmounts
     *
     * @return \Jackiedo\Cart\Details
     */
    protected function groupAnalysic($groupName, $withCartsHaveNoItems, $withItems, $withActions, $withTaxes, array $moneyAmounts = [])
    {
        $info = session($this->rootSessionName . '.' . $groupName, []);

        // If this is a group
        if ('cart' !== Arr::get($info, 'type')) {
            $extraInfo   = Arr::get($info, 'extra_info', []);
            $info        = Arr::except($info, ['extra_info']);
            $details     = new Details;
            $subsections = new Details;

            $details->put('type', 'group');
            $details->put('name', $groupName);

            foreach ($info as $key => $value) {
                // Get details of subsections
                $subInfo = $this->groupAnalysic($groupName . '.' . $key, $withCartsHaveNoItems, $withItems, $withActions, $withTaxes, $moneyAmounts);

                if ($subInfo instanceof Details) {
                    if ($subInfo->has(['subtotal', 'tax_amount'])) {
                        $moneyAmounts['subtotal']   = Arr::get($moneyAmounts, 'subtotal', 0) + $subInfo->get('subtotal', 0);
                        $moneyAmounts['tax_amount'] = Arr::get($moneyAmounts, 'tax_amount', 0) + $subInfo->get('tax_amount', 0);
                    }

                    if ($subInfo->has(['total'])) {
                        $moneyAmounts['total'] = Arr::get($moneyAmounts, 'total', 0) + $subInfo->get('total', 0);
                    }

                    $subsections->put($key, $subInfo);
                }
            }

            $details->put('items_count', $subsections->sum('items_count'));
            $details->put('quantities_sum', $subsections->sum(function ($section) {
                return $section->get('quantities_sum', $section->get('items_count'));
            }));

            if (!empty($moneyAmounts)) {
                foreach ($moneyAmounts as $key => $value) {
                    $details->put($key, $value);
                }
            }

            $details->put('subsections', $subsections);
            $details->put('extra_info', $extraInfo);

            // Return group details
            return $details;
        }

        // If this is a cart
        $cart = $this->newInstance($groupName);

        if (!$withCartsHaveNoItems && $cart->hasNoItems()) {
            return null;
        }

        return $cart->getDetails($withItems, $withActions, $withTaxes);
    }
}
