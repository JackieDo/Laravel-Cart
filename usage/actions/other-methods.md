# Other methods to work with actions
## For action instance
### Retrieve the cart instance that action belongs to.
**Method syntax:**

```php
/**
 * Get the cart instance that this action belongs to
 *
 * @return Jackiedo\Cart\Cart
 */
public function getCart();
```

### Retrieve the instance that action applied to
**Method syntax:**

```php
/**
 * Get parent node instance that this instance is applied to
 *
 * @return object
 */
public function getParentNode();
```

**Example:**

```php
$cart = Cart::name('shopping');
$item = $cart->addItem([
    // ...
]);

$action1 = $cart->applyAction([
    // ...
]);

$action2 = $item->applyAction([
    // ...
]);

print_r(get_class($action1->getParentNode()));                     // Jackiedo\Cart\Cart
print_r(($action1->getParentNode() === $cart) ? 'True' : 'False'); // True

print_r(get_class($action2->getParentNode()));                     // Jackiedo\Cart\Item
print_r(($action2->getParentNode() === $item) ? 'True' : 'False'); // True
```

### Get value of one or some extended information
**Method syntax:**

```php
/**
 * Get value of one or some extended informations of the current tax
 * using "dot" notation.
 *
 * @param  null|string|array $information The information want to get
 * @param  mixed             $default
 *
 * @return mixed
 */
public function getExtraInfo($information = null, $default = null)
```

**Example:**

```php
$action = $cart->applyAction([
    // ...
]);

return $action->getExtraInfo('date_time.added_at');
```

### Get value of one or some rules
**Method syntax:**

```php
/**
 * Get the formatted rules of this actions
 *
 * @param  string $rule    The specific rule or a set of rules
 * @param  mixed  $default The return value if the rule does not exist
 *
 * @return mixed
 */
public function getRules($rule = null, $default = null);
```

**Example:**

```php
$minAmountRule = $action->getRules('min_amount');
$limitRules    = $action->getRules(['min_amount', 'max_amount']);
```

### Check activation status of action
**Method syntax:**

```php
/**
 * Indicates whether this action is enabled
 *
 * @return boolean Return true if this action is self-activated and
 *                 is not disbaled by another action
 */
public function isEnabled();
```

### Check taxable status of action
**Method syntax:**

```php
/**
 * Indicates whether this action is taxable
 *
 * @return boolean Return true if parent node is taxable item
 *                 and the taxable rule is true
 */
public function isTaxable();
```

## For parent node instance
### Check for the existence of an action
**Method syntax:**

```php
/**
 * Determines if the action exists in the actions container by given action hash
 *
 * @param  string $actionHash The unique identifier of the action
 *
 * @return boolean
 */
public function hasAction($actionHash);
```

### Count the number of applied actions
**Method syntax:**

```php
/**
 * Count all actions in the actions container that match the given filter
 *
 * @param  mixed   $filter    Search filter
 * @param  boolean $complyAll Indicates that the results returned must satisfy
 *                            all the conditions of the filter at the same time
 *                            or that only parts of the filter.
 *
 * @return integer
 */
public function countActions($filter = null, $complyAll = true);
```

**Note:** The `$filter` and `$complyAll` parameters are used in the same way as the `getItems()` method.

### Sum amount of all applied actions
**Method syntax:**

```php
/**
 * Calculate the sum of action amounts in the actions container that match the given filter
 *
 * @param  mixed   $filter    Search filter
 * @param  boolean $complyAll Indicates that the results returned must satisfy
 *                            all the conditions of the filter at the same time
 *                            or that only parts of the filter.
 *
 * @return float
 */
public function sumActionsAmount($filter = null, $complyAll = true);
```

**Note:** The `$filter` and `$complyAll` parameters are used in the same way as the `getItems()` method.
