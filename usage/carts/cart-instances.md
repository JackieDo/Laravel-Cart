# The cart instances
You already know how to use the `name()` method to specify cart to work with. But keep in mind, it's just switching between cart names with a single cart instance. Example:

```php
// Select the cart with named is `shopping` and assign to the variable.
$shoppingCart = Cart::name('shopping');

// Select the cart with named is `wishlist` and assign to the variable.
$wishlistCart = Cart::name('wishlist');

// Get name of shopping cart
$name = $shoppingCart->getName(); // wishlist

// Compare carts
// True, because these two variables are just an object.
return $shoppingCart === $wishlistCart ? 'True' : 'False';
```

Therefore, if you want to work with different shopping carts through separate instances, use the following syntax:

**Method syntax:**

```php
/**
 * Create an another cart instance with the specific name
 *
 * @param  string|null $name The cart name
 *
 * @return Jackiedo\Cart\Cart
 */
$cart = Cart::newInstance($name = null);
```

**Example:**

```php
$cart1 = Cart::name('shopping');
$cart2 = Cart::newInstance('wishlist');
$cart3 = Cart::newInstance('wishlist');

// Get names
$name1 = $cart1->getName(); // shopping
$name2 = $cart2->getName(); // wishlist
$name3 = $cart3->getName(); // wishlist

// Compare the cart1 and cart2
// Will return False, because these two variables are separate objects
return $cart1 === $cart2 ? 'True' : 'False';

// Compare the cart2 and cart3
// Will return True, because these two variables are separate objects
// but have the same properties
return $cart2 === $cart3 ? 'True' : 'False';
```

**Tip:**
- Using the `name()` method is like having a person using two baskets in a supermarket.
- Using the `newInstance()` method is like having two people using two baskets or even using the same baskets in the supermarket.
