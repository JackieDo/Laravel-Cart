# Name of the cart
Laravel Cart supports working with multiple carts. This is useful for using different carts for different management purposes, such as shopping cart, recently viewed, compared items... Therefore, to be able to distinguish between the carts, each cart will have a unique name.

## Specify cart to work by name
To specify the cart you need to work with, use the following syntax:

**Method syntax:**

```php
/**
 * Select a cart to work with
 *
 * @param  string|null $name The cart name
 *
 * @return Jackiedo\Cart\Cart
 */
public function name($name);
```

**Example:**

```php
// Select the cart with named is `shopping` to work.
$cart = Cart::name('shopping');

// Perform a something with this instance
$cart->doSomething();

// Switch to `wishlist` cart and do something
Cart::name('wishlist')->doSomething();

// Do another thing also with `wishlist` cart
Cart::doAnother();

// Switch to `recently_viewed` cart and do something
Cart::name('recently_viewed')->doSomething();

// Go back to work with `shopping` cart
Cart::name('shopping')->doSomething();
```

**Note:**
- There is always a default cart name set in the configuration file (see [here](configuration#default-cart-name)). So, at the cart initialization line in the source code, if you don't specify a name for that cart, it will have your name as set in the configuration file.
- Always keep in mind that the current active cart is the cart with the name you last specified.

## Get the cart name
Whenever you need to know the name of the current cart, you use the following syntax:

**Method syntax:**

```php
/**
 * Get the current cart name
 *
 * @return string
 */
public function getName();
```

**Example:**

```php
// Select the cart with named is `shopping` to do something.
Cart::name('shopping')->doSomething();

// Switch to `wishlist` cart and do something
Cart::name('wishlist')->doSomething();

// get current cart name
$currentCartName = Cart::getName(); // wishlist
```
