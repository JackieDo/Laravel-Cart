# Update added item
To update an added item, you need to have that item's hash code (the `hash` attribute).

**Method syntax:**

```php
/**
 * Update an item in the cart
 *
 * @param  string    $itemHash   The unique identifier of the item
 * @param  int|array $attributes New quantity of item or array of new attributes to update
 * @param  boolean   $withEvent  Enable firing the event
 *
 * @return Jackiedo\Cart\Item|null
 */
public function updateItem($itemHash, $attributes = [], $withEvent = true);
```

**Example:**

```php
$cart = Cart::name('shopping');
$item = $cart->addItem([
    // ...
]);

$updatedItem = $cart->updateItem($item->getHash(), [
    'title'      => 'New title',
    'extra_info' => [
        'date_time.updated_at' => time()
    ]
]);
```

**Note:**
- You can only update the `title`, `quantity`, `price`, `taxable`, `options` and `extra_info` attributes.
- If you update one of the `price` and `options` attributes, the `hash` attribute will be changed.
