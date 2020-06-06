# Grouping the carts
## Why is it necessary?
Imagine, one day, you need to build an online E-commerce Exchange platform with many stores. Each stall needs a separate shopping cart. So how do you group the shopping carts into a group, separate from the cart of recently viewed articles, to browse all the carts in the group when needed? This feature was born to solve that problem.

## Name the group
This issue will be easily solved with the `name($name)` method of the cart instance. Just pass the `$name` parameter with a group name in front of the shopping cart name, separated by a dot. Example:

```php
Cart::name('shopping.shop_1')->doSomething();
Cart::name('shopping.shop_2')->doSomething();
Cart::name('shopping.shop_3')->doSomething();

Cart::name('recently_viewed')->doSomething();
```

So you have 4 carts and the `shop_1`,` shop_2` and `shop_3` carts have been grouped into a group called `shopping`. Meanwhile cart `recently_viewed` does not belong to any group.

**Note:**

- You cannot use the `extra_info` keyword to set a name for the group.
- You can create a group with multi levels. Example: `shopping.shop.id_shop_1`, `shopping.shop.id_shop_2`... As such, you will have the `shop` group put under the` shopping` group.

## Get the group name
**Method syntax:**

```php
/**
 * Get the group name of the cart
 *
 * @return string
 */
public function getGroupName();
```

## Check if the cart has been grouped
**Method syntax:**

```php
/**
 * Determines whether this cart has been grouped
 *
 * @return boolean
 */
public function hasBeenGrouped();
```

## Check if the cart belongs to a specific group
**Method syntax:**

```php
/**
 * Determines whether this cart is in the specific group
 *
 * @param  string  $groupName The specific group name
 *
 * @return boolean
 */
public function isInGroup($groupName);
```
