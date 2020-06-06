# Update applied tax
To update an applied tax, you need to have that tax's hash code (the `hash` attribute).

**Method syntax:**

```php
/**
 * Update a tax in the cart
 *
 * @param  string  $taxHash    The unique identifire of the tax instance
 * @param  array   $attributes The new attributes
 * @param  boolean $withEvent  Enable firing the event
 *
 * @return Jackiedo\Cart\Tax|null
 */
public function updateTax($taxHash, array $attributes = [], $withEvent = true);
```

**Example:**

```php
$cart = Cart::name('shopping');
$tax  = $cart->applyTax([
    // ...
]);

$updated = $cart->updateTax($tax->getHash(), [
    'title'      => 'New title',
    'extra_info' => [
        'date_time.updated_at' => time()
    ]
]);
```

**Note:** You can only update the `title`, `rate`, `extra_info` attributes.
