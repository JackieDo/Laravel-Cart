# Apply an action
**Method syntax:**

```php
/**
 * Add an action into the actions container
 *
 * @param  array   $attributes The action attributes
 * @param  boolean $withEvent  Enable firing the event
 *
 * @return Jackiedo\Cart\Action|null
 */
public function applyAction(array $attributes = [], $withEvent = true);
```

The result of this method is an instance of the `Jackiedo\Cart\Action` class. However, you cannot instantiate an object from this class and treat it as an applied action. A properly applied action can only be obtained via the above `applyAction()` method. You need to pass this method an array of the following attributes:

* `id`:
    - Description: Raw id of the action, such as information from the id field in the database.
    - Type: string | int
    - Required: true
    - Default: null
* `title`:
    - Description: The name of the action.
    - Type: string
    - Required: true
    - Default: null
* `group`:
    - Description: Grouping for action.
    - Type: string
    - Required: false
    - Default: 'unknown'
* `value`:
    - Description: The value to change the amount of instance that this action is applied to.
    - Type: float | string (percentage)
    - Required: false
    - Default: 0
* `target`:
    - Description: The target that this action wants to change.
    - Type: string
    - Required: false
    - Default: 'items\_subtotal' (for the cart) | 'total\_price' (for the item)
* `rules`:
    - Description: The handling rules for action.
    - Type: array
    - Required: false
    - Default: The `default_action_rules` setting in the configuration file (see [here](configuration#default-rules-of-actions))
* `extra_info`:
    - Description: Store other extended information.
    - Type: array
    - Required: false
    - Default: []

**Example:**

```php
$cart = Cart::name('shopping');
$item = $cart->addItem([
    // ...
]);

$item->applyAction([
    'group'      => 'Discount',
    'id'         => 1,
    'title'      => 'Sale 10%',
    'value'      => '-10%',
    'extra_info' => [
        'description' => 'Winter sale program'
    ]
]);

$cart->applyAction([
    'group' => 'Additional costs',
    'id'    => 123,
    'title' => 'Shipping cost',
    'value' => 20
]);
```

**Note:** Always pay attention to the order in which actions are applied to each instance because different orders may change the money amount ​​differently.

## The attributes of action
An applied action contains the attributes that you passed into the `applyAction()` method and has some other special attributes:

* `hash`:
    - Description: The unique identifier of action in the cart.
    - Type: string
* `amount`:
    - Description: The calcualted amount for the action.
    - Type: float

**Note:** The `hash` attribute is used to identify the different taxes in the cart. This information is made up of the `id` and `group` attributes. This means that you can apply the same action with same `id`, but with different `group` attribute.

## Retrieve action attributes
You can access the attributes of the applied action the same way you access the attributes of an item. More references [here](usage/items/add-item#retrieve-item-attributes).
