# Get money amounts of the cart
## Get subtotal amount
The subtotal amount is the amount of the cart after adding the sum of the actions amount of the cart.

**Method syntax:**

```php
/**
 * Get the subtotal amount of all items in the items container
 *
 * @return float
 */
public function getSubtotal();
```

**Example:**

```php
$cart = Cart::name('shopping');

return $cart->getSubtotal();
```

## Get total amount
The total amount is the final amount of the cart after tax.

**Method syntax:**

```php
/**
 * Get the total amount of the current cart.
 *
 * @return float
 */
public function getTotal();
```

**Example:**

```php
$cart = Cart::name('shopping');

return $cart->getTotal();
```
