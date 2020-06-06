# Update applied action
To update an applied action, you need to have that action's hash code (the `hash` attribute).

**Method syntax:**

```php
/**
 * Update an action in the actions container
 *
 * @param  string  $actionHash The unique identifier of the action
 * @param  array   $attributes The new attributes
 * @param  boolean $withEvent  Enable firing the event
 *
 * @return Jackiedo\Cart\Action|null
 */
public function updateAction($actionHash, array $attributes = [], $withEvent = true);
```

**Example:**

```php
$cart = Cart::name('shopping');
$action  = $cart->applyAction([
    // ...
]);

$updated = $cart->updateAction($action->getHash(), [
    'title'      => 'New title',
    'extra_info' => [
        'date_time.updated_at' => time()
    ]
]);
```

**Note:** You can only update the `title`, `group`, `value`, `rules`, `extra_info` attributes.
