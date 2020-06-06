# Commercial or non-commercial cart
## Concept
A commercial cart as a cart containing money-related information. This means that the money amounts of the cart may change when we intervene in the cart.

Non-commercial carts are the carts have no information regarding money. This means that these carts only record the existence of the added items, the information of quantity, price... will always be 0.

Designating a cart as non-commercial is simply to eliminate unnecessary confusing information, reduce storage capacity, and send data through requests.

## Set the commercial status
Whenever initialize a cart, it can be used for commercial purposes or not. That depends on whether the cart name is listed in the `none_commercial_carts` setting in the configuration file (see [here](configuration#none-commercial-carts)).

Alternatively, you can set this status on the fly in your code with the following syntax:

**Method syntax:**

```php
/**
 * Change whether the cart status is used for commercial or not.
 *
 * @param  boolean $status
 * @return Jackiedo\Cart\Cart
 */
public function useForCommercial($status = true);
```

**Example:**

```php
$shoppingCart = Cart::name('shopping')->useForCommercial();
$shoppingCart->doSomething();

$recentlyViewed = Cart::newInstance('recently_viewed')->useForCommercial(false);
$recentlyViewed->doSomething();
```

**Note:** You can only set this status if the cart is empty, meaning that no items have been added, no taxes or actions have been applied.

## Check the commercial status
**Method syntax:**

```php
/**
 * Determines if current cart is used for commcercial
 *
 * @return boolean
 */
public function isCommercialCart();
```

**Example:**

```php
$cart = Cart::name('recently_viewed')->useForCommercial(false);

print_r($cart->isCommercialCart() ? 'True' : 'False'); // False
```
