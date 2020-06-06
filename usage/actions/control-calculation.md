# Control calculation of the action
As you known, when applying multiple actions to an instance, actions may or may not influence each other. So we need to control the calculation of actions strictly.

To do this, you need to know that each action has three information affecting the calculation method. Which is the `target` attribute, the `rules` attribute and the order of applying. We will go find out each information.

## The target attribute
This attribute specifies which amount of action will be calculated based on. Available values of this attribute are `items_subtotal`, `total_price` and `price`.

If the action is applied to the cart, this attribute will always be set to `items_subtotal`, you don't need to change it. But if the action is applied to the item, you have the choice to set the value of this attribute to `total_price` or` price`.

**Example 1 - Apply action to the cart - Value is float type:**

```php
$cart = Cart::name('shopping');

$item1 = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example title 1',
    'quantity' => 2,
    'price'    => 200
]);

$item2 = $cart->addItem([
    'id'       => 2,
    'title'    => 'Example title 2',
    'quantity' => 2,
    'price'    => 200
]);

$action = $cart->applyAction([
    'id'    => 1,
    'title' => 'Discount 10$',
    'value' => -10
]);

print_r($action->getAmount());      // -10

print_r($cart->getItemsSubtotal()); // 800
print_r($cart->sumActionsAmount()); // -10
print_r($cart->getSubtotal());      // 790
```

**Example 1 - Apply action to the cart - Value is percentage:**

```php
$cart = Cart::name('shopping');

$item1 = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example title 1',
    'quantity' => 2,
    'price'    => 200
]);

$item2 = $cart->addItem([
    'id'       => 2,
    'title'    => 'Example title 2',
    'quantity' => 2,
    'price'    => 200
]);

$action = $cart->applyAction([
    'id'    => 1,
    'title' => 'Discount 10%',
    'value' => '-10%'
]);

print_r($action->getAmount());      // -80

print_r($cart->getItemsSubtotal()); // 800
print_r($cart->sumActionsAmount()); // -80
print_r($cart->getSubtotal());      // 720
```

**Example 3 - Apply action to the item - Value is float type:**

```php
$cart = Cart::name('shopping');

$item1 = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example title 1',
    'quantity' => 2,
    'price'    => 200
]);

$action1 = $item1->applyAction([
    'id'     => 1,
    'title'  => 'Discount 10$',
    'value'  => -10,
    'target' => 'total_price'
]);

$item2 = $cart->addItem([
    'id'       => 2,
    'title'    => 'Example title 2',
    'quantity' => 2,
    'price'    => 200
]);

$action2 = $item2->applyAction([
    'id'     => 1,
    'title'  => 'Discount 10$',
    'value'  => -10,
    'target' => 'price'
]);

print_r($action1->getAmount());      // -10
print_r($item1->getTotalPrice());    // 400
print_r($item1->sumActionsAmount()); // -10
print_r($item1->getSubtotal());      // 390

print_r($action2->getAmount());      // -20
print_r($item2->getTotalPrice());    // 400
print_r($item2->sumActionsAmount()); // -20
print_r($item2->getSubtotal());      // 380

print_r($cart->getItemsSubtotal());  // 770
print_r($cart->sumActionsAmount());  // 0
print_r($cart->getSubtotal());       // 770
```

**Example 4 - Apply action to the item - Value is percentage:**

```php
$cart = Cart::name('shopping');

$item1 = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example title 1',
    'quantity' => 2,
    'price'    => 200
]);

$action1 = $item1->applyAction([
    'id'     => 1,
    'title'  => 'Discount 10%',
    'value'  => '-10%',
    'target' => 'total_price'
]);

$item2 = $cart->addItem([
    'id'       => 2,
    'title'    => 'Example title 2',
    'quantity' => 2,
    'price'    => 200
]);

$action2 = $item2->applyAction([
    'id'     => 1,
    'title'  => 'Discount 10%',
    'value'  => '-10%',
    'target' => 'price'
]);

print_r($action1->getAmount());      // -40
print_r($item1->getTotalPrice());    // 400
print_r($item1->sumActionsAmount()); // -40
print_r($item1->getSubtotal());      // 360

print_r($action2->getAmount());      // -40
print_r($item2->getTotalPrice());    // 400
print_r($item2->sumActionsAmount()); // -40
print_r($item2->getSubtotal());      // 360

print_r($cart->getItemsSubtotal());  // 720
print_r($cart->sumActionsAmount());  // 0
print_r($cart->getSubtotal());       // 720
```

## The rules attribute
This attribute is set as an array of rules. Available rules are:

- `enable`,
- `taxable`,
- `allow_others_disable`,
- `disable_others`,
- `include_calculations`,
- `max_amount`,
- `min_amount`

The default value of the rules can be set in the configuration file (see [here](configuration#default-rules-of-actions)) or using the `setDefaultActionRules()` method of the cart instance with the syntax:

```php
/**
 * Set default action rules for the cart.
 * This is only possible if the cart is empty
 *
 * @param array $rules The default action rules
 */
public function setDefaultActionRules(array $rules = []);
```

**Note:** The above method is only available when the cart is empty (not adding items, not applying actions and taxes).

### enable
- Type: boolean
- Available values: true | false

This rule allows to enable or disable the current action. It is more useful than having to add or delete actions repeatedly, which will change the order of action. The amount of an action is only calculated when that action is enabled.

**Example:**

```php
$cart = Cart::name('shopping');
$item = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example product',
    'quantity' => 2,
    'price'    => 200
]);

$action1 = $cart->applyAction([
    'id'    => 1,
    'title' => 'Discount 10%',
    'value' => '-10%',
    'rules' => [
        'enable' => false
    ]
]);

$action2 = $cart->applyAction([
    'id'    => 2,
    'title' => 'Discount 10% again',
    'value' => '-10%',
    'rules' => [
        'enable' => true
    ]
]);

print_r($action1->getAmount());     // 0
print_r($action2->getAmount());     // -40
print_r($cart->sumActionsAmount()); // -40
print_r($cart->getSubtotal());      // 360 (400 - 40)
```

**Note:**
- One action may disable another.
- One action may be disabled by another action.

### allow\_others_disable
- Type: boolean
- Available values: true | false

If you set this rule to true, the activation status of the action may be changed by another action even though the `enable` rule is true. If you don't want this to happen, set the value to false.

### disable_others
- Type: null | string
- Available values: null | 'previous\_actions' | 'same\_group\_previous\_actions' | 'previous\_groups'

This rule allows to disable one or several other actions when they are applied to the same instance. Depending on the value of the rule, we determine which actions will be disabled.

- `null`: Do not disable any rules.
- `previous_actions`: Disable pre-applied actions if they allow and irrespective of group.
- `same_group_previous_actions`: Disable pre-applied actions if they allow and in the same group as this one.
- `previous_groups`: Disable the actions that are in the group before this action group if they allow.

**Example:**

```php
$cart = Cart::name('shopping');
$item = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example product',
    'quantity' => 2,
    'price'    => 200
]);

$action1 = $cart->applyAction([
    'group' => 'discount',
    'id'    => 1,
    'title' => 'Discount 10%',
    'value' => '-10%',
    'rules' => [
        'enable'               => true,
        'allow_others_disable' => true
    ]
]);

$action2 = $cart->applyAction([
    'group' => 'discount',
    'id'    => 2,
    'title' => 'Discount 10% again',
    'value' => '-10%',
    'rules' => [
        'enable'               => true,
        'allow_others_disable' => true
        'disable_others'       => 'previous_actions'
    ]
]);

$action3 = $cart->applyAction([
    'group' => 'additional_costs',
    'id'    => 3,
    'title' => 'Shipping cost 20$',
    'value' => 20,
    'rules' => [
        'enable'         => true,
        'disable_others' => 'same_group_previous_actions',
    ]
]);

// $action1 will be disabled by $action2
// because it is pre-applied $action2

// $action3 will not disable any action
// because $action1 and $action2 are not
// in same group with it

print_r($action1->getAmount());     // 0
print_r($action2->getAmount());     // -40
print_r($action3->getAmount());     // 20
print_r($cart->sumActionsAmount()); // -20 (- 40 + 20)
print_r($cart->getSubtotal());      // 380 (400 - 20)
```

### include_calculations
- Type: null | string
- Available values: null | 'previous_actions' | 'same_group_previous_actions' | 'previous_groups'

This rule allows to includes the calculation of one or more actions into the target before the calculation for this action. Depending on the value of the rule, we determine which calculation of the action will be included in the target.

- `null`: Always calculate based on the target.
- `previous_actions`: Calculate after adding the calculation of pre-applied actions to the target, irrespective of group.
- `same_group_previous_actions`: Calculate after adding the calculation of pre-applied actions to the target if they are in the same group as this one.
- `previous_groups`: Calculate after adding the calculation of actions in the group before this action group to the target.

**Example:**

```php
$cart = Cart::name('shopping');
$item = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example product',
    'quantity' => 2,
    'price'    => 200
]);

$action1 = $cart->applyAction([
    'group' => 'discount',
    'id'    => 1,
    'title' => 'Discount 10%',
    'value' => '-10%',
    'rules' => [
        'enable'               => true,
        'disable_others'       => null,
        'include_calculations' => 'previous_actions'
    ]
]);

$action2 = $cart->applyAction([
    'group' => 'discount',
    'id'    => 2,
    'title' => 'Discount 10% again',
    'value' => '-10%',
    'rules' => [
        'enable'               => true,
        'disable_others'       => null,
        'include_calculations' => 'previous_actions'
    ]
]);

$action3 = $cart->applyAction([
    'group' => 'additional_costs',
    'id'    => 3,
    'title' => 'Service charge 10%',
    'value' => '10%',
    'rules' => [
        'enable'               => true,
        'disable_others'       => null,
        'include_calculations' => null
    ]
]);

print_r($action1->getAmount());     // -40 (-10% of 400)
print_r($action2->getAmount());     // -36 (-10% of 360)
print_r($action3->getAmount());     // 40 (10% of 400)
print_r($cart->sumActionsAmount()); // -36 (- 40 - 36 + 40)
print_r($cart->getSubtotal());      // 364 (400 - 36)
```

### max\_amount
- Type: null | float

This rule allows to determine the limit of the calculation. It only works when the `value` attribute of action is a percentage. Example:

```php
$cart = Cart::name('shopping');
$item = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example product',
    'quantity' => 2,
    'price'    => 200
]);

$action = $cart->applyAction([
    'group' => 'discount',
    'id'    => 1,
    'title' => 'Discount 10%',
    'value' => '-10%',
    'rules' => [
        'max_amount' => -30 // Discount 10% but do not exceed -30
    ]
]);

print_r($action->getAmount());      // -30
print_r($cart->sumActionsAmount()); // -30
print_r($cart->getSubtotal());      // 370 (400 - 30)
```

### min_amount
Similar to [max_amount](#max_amount).

### taxable
- Type: boolean
- Available values: true | false

This rule indicates whether the amount of the action is taxable. It only works if the built-in taxing system has been activated.

**Example:**

```php
$cart = Cart::name('shopping');
$item = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example product',
    'quantity' => 2,
    'price'    => 200,
    'taxable'  => true
]);

$action1 = $cart->applyAction([
    'group' => 'discount',
    'id'    => 1,
    'title' => 'Discount 10%',
    'value' => '-10%',
]);

$action2 = $cart->applyAction([
    'group' => 'additional_costs',
    'id'    => 1,
    'title' => 'Shipping costs',
    'value' => '20',
    'rules'  => [
        'taxable' => false // Not taxable
    ]
]);

$tax = $cart->applyTax([
    'id'    => 1,
    'title' => 'VAT 10%',
    'rate'  => 10
]);

print_r($action1->getAmount());     // -40
print_r($action2->getAmount());     // 20
print_r($cart->sumActionsAmount()); // -20
print_r($cart->getSubtotal());      // 380 (400 - 20)
print_r($cart->getTaxableAmount()); // 360 (400 - 40)
print_r($cart->getTaxAmount());     // 36 (360 * 10%)
print_r($cart->getTotal());         // 416 (380 + 36)
```

**Note:** Based on the calculation method of the built-in tax system, if this action applies to an item that is set to be non-taxable, this action is also not taxable.

**Example:**

```php
$cart = Cart::name('shopping')->useBuiltinTax(true);
$item = $cart->addItem([
    'id'       => 1,
    'title'    => 'Example product',
    'quantity' => 2,
    'price'    => 200,
    'taxable'  => false // look at here
]);

$action = $item->applyAction([
    'group' => 'discount',
    'id'    => 1,
    'title' => 'Discount 10%',
    'value' => '-10%',
    'rules' => [
        'taxable' => true // look at here
    ]
]);

$tax = $cart->applyTax([
    'id'    => 1,
    'title' => 'VAT 10%',
    'rate'  => 10
]);

print_r($action->isTaxable() ? 'True' : 'False'); // False
print_r($cart->getSubtotal());                    // 360
print_r($cart->getTaxableAmount());               // 0
print_r($cart->getTaxAmount());                   // 0
print_r($cart->getTotal());                       // 360
```

## The order of applying
As you have known from the `rules` attribute guide, the order of applying affects the calculation of the amount of an action unless you set the `include_calculations` rule for all actions to `null`. The order of applying an action depends on when you use the `applyAction()` method in your source code and the order of the action group.

By default, this package does not specify the order of any action groups, that means no groups will be prioritized. Now, to specify the order of the group, you have two ways:

- Solution 1: Set `action_groups_order` entry in the configuration file (see [here](configuration#action-groups-order)). This setting will apply to all carts when initialized.
- Solution 2: Set action groups order on the fly for each cart using the `setActionGroupsOrder()` method with the syntax:

```php
/**
 * Set action groups order for the cart
 *
 * @param  array $order The action groups order
 *
 * @return $this
 */
public function setActionGroupsOrder(array $order = []);
```

**Example:**

```php
$cart = Cart::name('shopping');

$cart->setActionGroupsOrder([
    'seller_discount',
    'exchange_floor_discount',
    'service_charge',
    // ...
]);

$action1 = $cart->applyAction([
    'id'    => 1,
    'group' => 'exchange_floor_discount',
    // ...
]);

$action2 = $cart->applyAction([
    'id'    => 2,
    'group' => 'seller_discount',
    // ...
]);

// $action2 will be considered to be applied before $action1
print_r($action1->isPreviousOf($action2) ? 'True' : 'False'); // False
print_r($action1->isBehindOf($action2) ? 'True' : 'False');   // True
```

**Note:**
- You can set the order of action group at any time.
- This setting simply specifies the priority of the action groups by listing the group names in descending order of priority.
