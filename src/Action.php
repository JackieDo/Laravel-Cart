<?php

namespace Jackiedo\Cart;

use Illuminate\Support\Arr;
use Jackiedo\Cart\Contracts\ActionHandler;
use Jackiedo\Cart\Contracts\CartNode;
use Jackiedo\Cart\Exceptions\InvalidArgumentException;
use Jackiedo\Cart\Traits\CanBeCartNode;

class Action implements CartNode
{
    use CanBeCartNode;

    /**
     * The attributes of action.
     *
     * @var array
     */
    protected $attributes = [
        'group'      => 'unknown',
        'id'         => null,
        'title'      => null,
        'target'     => null,
        'value'      => 0,
        'rules'      => [],
        'extra_info' => [],
    ];

    /**
     * The name of the accepted class is the creator.
     *
     * @var array
     */
    protected $acceptedCreators = [
        ActionsContainer::class,
    ];

    /**
     * Indicates whether this action belongs to a taxable cart.
     *
     * @var bool
     */
    protected $enabledBuiltinTax = false;

    /**
     * Stores the number used to sort.
     *
     * @var int
     */
    protected $orderNumber;

    /**
     * The constructor.
     *
     * @param array $attributes The action attributes
     */
    public function __construct(array $attributes = [])
    {
        // Stores the creator
        $this->storeCreator(0, function ($creator, $caller) {
            $cart                      = $this->getCart();
            $this->enabledBuiltinTax   = $cart->isEnabledBuiltinTax();
            $this->attributes['rules'] = $cart->getConfig('default_action_rules', []);
        });

        // Initialize attributes
        $this->initAttributes($attributes);
    }

    /**
     * Update attributes of this action instance.
     *
     * @param array $attributes The new attributes
     * @param bool  $withEvent  Enable firing the event
     *
     * @return $this
     */
    public function update(array $attributes = [], $withEvent = true)
    {
        // Determines the caller that called this method
        $caller      = getCaller();
        $callerClass = Arr::get($caller, 'class');
        $creator     = $this->getCreator();

        // If the caller is not the creator of this instance
        if ($callerClass !== get_class($creator)) {
            return $creator->updateAction($this->getHash(), $attributes, $withEvent);
        }

        // Filter the allowed attributes to be updated
        $attributes = Arr::only($attributes, ['title', 'group', 'value', 'rules', 'extra_info']);

        // Validate the input
        $this->validate($attributes);

        // Stores the input into attributes
        $this->setAttributes($attributes);

        return $this;
    }

    /**
     * Get details of the action as a collection.
     *
     * @return \Jackiedo\Cart\Details
     */
    public function getDetails()
    {
        $details = [
            'hash'   => $this->getHash(),
            'group'  => $this->getGroup(),
            'id'     => $this->getId(),
            'title'  => $this->getTitle(),
            'target' => $this->getTarget(),
            'value'  => $this->getValue(),
            'rules'  => new Details($this->getRules()),
        ];

        $details['enabled'] = $this->isEnabled();

        if ($this->enabledBuiltinTax) {
            $details['taxable'] = $this->isTaxable();
        }

        $details['amount']     = $this->getAmount();
        $details['extra_info'] = new Details($this->getExtraInfo());

        return new Details($details);
    }

    /**
     * Determines which values ​​to filter.
     *
     * @return array
     */
    public function getFilterValues()
    {
        return array_merge([
            'hash'    => $this->getHash(),
            'rules'   => $this->getRules(),
            'enabled' => $this->isEnabled(),
            'taxable' => $this->isTaxable(),
        ], Arr::only($this->attributes, ['group', 'id', 'title', 'value', 'extra_info']));
    }

    /**
     * Return the unique identifier of this action.
     *
     * @return string
     */
    public function getHash()
    {
        return 'action_' . md5($this->attributes['id'] . $this->attributes['group']);
    }

    /**
     * Get the id used to sort.
     *
     * @return string
     */
    public function getOrderId()
    {
        $groupsOrder    = $this->getConfig('action_groups_order', []);
        $thisGroupOrder = array_search($this->attributes['group'], $groupsOrder);

        if (false !== $thisGroupOrder) {
            return '1.' . $thisGroupOrder . '.' . $this->orderNumber;
        }

        return '2.0.' . $this->orderNumber;
    }

    /**
     * Get the formatted rules of this actions.
     *
     * @param null|string $rule    The specific rule or a set of rules
     * @param mixed       $default The return value if the rule does not exist
     *
     * @return mixed
     */
    public function getRules($rule = null, $default = null)
    {
        // Get original rules
        $originalRules = $this->attributes['rules'];

        // If rules is instance of ActionHandler
        if ($originalRules instanceof ActionHandler) {
            $originalRules = call_user_func_array([$originalRules, 'cartActionHandle'], [$this]);
            $originalRules = array_merge($this->getConfig('default_action_rules', []), $originalRules);
        }

        // Format the rules
        $rules = [
            'enable'               => (bool) Arr::get($originalRules, 'enable', true),
            'taxable'              => (bool) Arr::get($originalRules, 'taxable', true),
            'allow_others_disable' => (bool) Arr::get($originalRules, 'allow_others_disable', true),
            'disable_others'       => Arr::get($originalRules, 'disable_others'),
            'include_calculations' => Arr::get($originalRules, 'include_calculations'),
            'max_amount'           => Arr::get($originalRules, 'max_amount'),
            'min_amount'           => Arr::get($originalRules, 'min_amount'),
        ];

        if (!in_array($rules['disable_others'], [null, 'previous_actions', 'same_group_previous_actions', 'previous_groups'])) {
            $rules['disable_others'] = null;
        }

        if (!in_array($rules['include_calculations'], [null, 'previous_actions', 'same_group_previous_actions', 'previous_groups'])) {
            $rules['include_calculations'] = null;
        }

        if (!is_null($rules['max_amount'])) {
            $rules['max_amount'] = floatval($rules['max_amount']);
        }

        if (!is_null($rules['min_amount'])) {
            $rules['min_amount'] = floatval($rules['min_amount']);
        }

        // Return result
        if (is_null($rule)) {
            return $rules;
        }

        if (is_array($rule)) {
            return Arr::only($rules, $rule);
        }

        return Arr::get($rules, $rule, $default);
    }

    /**
     * Indicates whether this action is taxable.
     *
     * @return bool Return true if parent node is taxable item
     *              and the taxable rule is true
     */
    public function isTaxable()
    {
        if (!$this->enabledBuiltinTax) {
            return $this->getRules('taxable', true);
        }

        $parentNode = $this->getParentNode();

        if ($parentNode instanceof Item && !$parentNode->isTaxable()) {
            return false;
        }

        return $this->getRules('taxable', true);
    }

    /**
     * Determines if this action is self enabled through the rules attribute.
     *
     * @return bool
     */
    public function isSelfActivated()
    {
        return $this->getRules('enable', true);
    }

    /**
     * Determines if this action is disabled by one of the following action.
     *
     * @return bool
     */
    public function isDeactivated()
    {
        // If this action do not allow disabled
        if (!$this->getRules('allow_others_disable', true)) {
            return false;
        }

        // Get the behind actions that could be deactivator of this action
        $container    = $this->getCreator();
        $deactivators = $container->filter(function ($action) {
            if ($this->isPreviousOf($action) && $action->isEnabled()) {
                $actionDisableRule = $action->getRules('disable_others');

                if ('previous_actions' === $actionDisableRule) {
                    return true;
                }

                if ('same_group_previous_actions' === $actionDisableRule && $this->isSameGroupAs($action)) {
                    return true;
                }

                if ('previous_groups' === $actionDisableRule && !$this->isSameGroupAs($action)) {
                    return true;
                }

                if (is_array($actionDisableRule) && in_array($this->getGroup(), $actionDisableRule)) {
                    return true;
                }
            }

            return false;
        });

        // If there does not exist any deactivator action
        if ($deactivators->isEmpty()) {
            return false;
        }

        // Otherwise
        return true;
    }

    /**
     * Indicates whether this action is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSelfActivated() && !$this->isDeactivated();
    }

    /**
     * Determines if this action takes place before a specific action.
     *
     * @param \Jackiedo\Cart\Action $action The specific action
     *
     * @return bool
     */
    public function isPreviousOf(Action $action)
    {
        return ($this->getHash() != $action->getHash()) && ($this->getOrderId() < $action->getOrderId());
    }

    /**
     * Determines if this action takes place after a specific action.
     *
     * @param \Jackiedo\Cart\Action $action The specific action
     *
     * @return bool
     */
    public function isBehindOf(Action $action)
    {
        return ($this->getHash() != $action->getHash()) && ($this->getOrderId() > $action->getOrderId());
    }

    /**
     * Determines if this action is in the same group as the specific action.
     *
     * @param \Jackiedo\Cart\Action $action The specific action
     *
     * @return bool
     */
    public function isSameGroupAs(Action $action)
    {
        return $this->getGroup() === $action->getGroup();
    }

    /**
     * Get the amount of this action.
     *
     * @return float
     */
    public function getAmount()
    {
        $rules = $this->getRules();

        if (!$rules['enable'] || $this->isDeactivated()) {
            return 0;
        }

        // Prepare data
        $parentNode        = $this->getParentNode();
        $isPercentageValue = $this->isPercentage($this->attributes['value']);
        $value             = floatval($this->attributes['value']);
        $target            = $this->attributes['target'];
        $inclusiveAmount   = $rules['include_calculations'];

        // Calculate target amount
        $targetAmount = floatval(('items_subtotal' === $target) ? $parentNode->getItemsSubtotal() : $parentNode->getTotalPrice());
        $targetAmount = !is_null($inclusiveAmount) ? $targetAmount + $this->calcInclusiveAmount($inclusiveAmount) : $targetAmount;

        // Calculate action amount based on value and target amount
        if ($isPercentageValue) {
            $amount     = $targetAmount * ($value / 100);
            $maxAmount  = $rules['max_amount'];
            $minAmount  = $rules['min_amount'];
            $isNegative = $amount < 0;

            if (!is_null($minAmount)) {
                $amount = $isNegative ? min($amount, $minAmount) : max($amount, $minAmount);
            }

            if (!is_null($maxAmount)) {
                $amount = $isNegative ? max($amount, $maxAmount) : min($amount, $maxAmount);
            }
        } else {
            $amount = ('price' === $target) ? $parentNode->getQuantity() * $value : $value;
        }

        return max(0 - $targetAmount, $amount);
    }

    /**
     * Calculates the inclusive amount corresponding to the target of the action.
     *
     * @param string $type The included type
     *
     * @return float
     */
    protected function calcInclusiveAmount($type)
    {
        $container = $this->getCreator();

        // If container is empty
        if ($container->isEmpty()) {
            return 0;
        }

        // If the included type is all previous groups
        if ('previous_groups' === $type) {
            return $container->sum(function ($action) {
                $isPreviousAction = $this->isBehindOf($action);
                $isAnotherGroup   = !$this->isSameGroupAs($action);

                return ($isPreviousAction && $isAnotherGroup) ? $action->getAmount() : 0;
            });
        }

        // If the included type is all previous actions
        if ('previous_actions' === $type) {
            return $container->sum(function ($action) {
                $isPreviousAction = $this->isBehindOf($action);

                return ($isPreviousAction) ? $action->getAmount() : 0;
            });
        }

        // If the included type is all same group previous actions
        if ('same_group_previous_actions' === $type) {
            return $container->sum(function ($action) {
                $isPreviousAction = $this->isBehindOf($action);
                $isSameGroup      = $this->isSameGroupAs($action);

                return ($isPreviousAction && $isSameGroup) ? $action->getAmount() : 0;
            });
        }

        // If included type is an array of groups
        if (is_array($type)) {
            return $container->sum(function ($action) use ($type) {
                $isPreviousAction = $this->isBehindOf($action);
                $inAcceptedGroups = in_array($action->getGroup(), $type);

                return ($isPreviousAction && $inAcceptedGroups) ? $action->getAmount() : 0;
            });
        }

        return 0;
    }

    /**
     * Initialize the attributes.
     *
     * @param array $attributes The action attributes
     *
     * @return $this
     */
    protected function initAttributes(array $attributes = [])
    {
        // Add the missing default values ​​for the input
        $acceptedAttributes = array_keys($this->attributes);
        $attributes         = array_merge($this->attributes, Arr::only($attributes, $acceptedAttributes));

        // Validate the input
        $this->validate($attributes);

        // Stores the input into attributes
        $this->setAttributes($attributes);

        // Creates the sort number
        $this->orderNumber = intval(microtime(true) * 1000000);

        return $this;
    }

    /**
     * Set value for the attributes of this instance.
     *
     * @param array $attributes
     *
     * @return void
     */
    protected function setAttributes(array $attributes = [])
    {
        foreach ($attributes as $attribute => $value) {
            switch ($attribute) {
                case 'target':
                    $this->setTarget($value);
                    break;

                case 'rules':
                    $this->setRules($value);
                    break;

                case 'extra_info':
                    $this->setExtraInfo($value);
                    break;

                default:
                    $this->attributes[$attribute] = $value;
                    break;
            }
        }
    }

    /**
     * Set value for the target attribute.
     *
     * @param string $value
     *
     * @return void
     */
    protected function setTarget($value)
    {
        $parentNode = $this->getParentNode();

        if ($parentNode instanceof Cart) {
            $this->attributes['target'] = 'items_subtotal';
        } else {
            if (in_array($value, ['total_price', 'price'])) {
                $this->attributes['target'] = $value;
            } else {
                $this->attributes['target'] = 'total_price';
            }
        }
    }

    /**
     * Set value for the rules attribute.
     *
     * @param mixed $value
     *
     * @return void
     */
    protected function setRules($value)
    {
        $this->attributes['rules'] = is_array($value) ? array_merge($this->getRules(), $value) : $value;
    }

    /**
     * Determines whether the input is a percentage string.
     *
     * @param string $input
     *
     * @return bool
     */
    protected function isPercentage($input)
    {
        if (!is_string($input)) {
            return false;
        }

        return '%' == substr($input, -1);
    }

    /**
     * Validates the input.
     *
     * @param array $attributes Array of input
     *
     * @return void
     *
     * @throws Jackiedo\Cart\Exceptions\InvalidArgumentException
     */
    protected function validate(array $attributes = [])
    {
        if (array_key_exists('id', $attributes) && empty($attributes['id'])) {
            throw new InvalidArgumentException('The id attribute of the action is required.');
        }

        if (array_key_exists('title', $attributes) && (!is_string($attributes['title']) || empty($attributes['title']))) {
            throw new InvalidArgumentException('The title attribute of the action is required.');
        }

        if (array_key_exists('group', $attributes) && !is_string($attributes['group'])) {
            throw new InvalidArgumentException('The group attribute of the action must be a string.');
        }

        if (array_key_exists('target', $attributes) && !is_null($attributes['target']) && !is_string($attributes['target'])) {
            throw new InvalidArgumentException('The target attribute of the action must be null or a string.');
        }

        if (array_key_exists('value', $attributes) && !preg_match('/^\-{0,1}\d+(\.{0,1}\d+)?\%{0,1}$/', $attributes['value'])) {
            throw new InvalidArgumentException('The value attribute of the action must be a float numeric or percentage.');
        }

        if (array_key_exists('rules', $attributes) && !is_array($attributes['rules']) && !($attributes['rules'] instanceof ActionHandler)) {
            throw new InvalidArgumentException('The rules attribute of the action must be an array or an instance of ' . ActionHandler::class . '.');
        }

        if (array_key_exists('extra_info', $attributes) && !is_array($attributes['extra_info'])) {
            throw new InvalidArgumentException('The extra_info attribute of the action must be an array.');
        }
    }
}
