# Get cart group details as Laravel Collection
**Method syntax:**

```php
/**
 * Get all information of cart group as a collection
 *
 * @param  string  $groupName            The group part from cart name
 * @param  boolean $withCartsHaveNoItems Include carts have no items in the result
 * @param  boolean $withItems            Include details of added items in the result
 * @param  boolean $withActions          Include details of applied actions in the result
 * @param  boolean $withTaxes            Include details of applied taxes in the result
 *
 * @return Jackiedo\Cart\Details
 */
public function getGroupDetails(
    $groupName = null,
    $withCartsHaveNoItems = false,
    $withItems = true,
    $withActions = true,
    $withTaxes = true
);
```

**Note:** If the `$groupName` parameter is not passed, the details of the parent group of current cart will be returned.

**Example:**

```php
$cart = Cart::name('shopping.shop.abc');

// Retrieve details of the 'shopping.shop' group
$shoppingShopDetails = $cart->getGroupDetails();

// Retrieve details of the 'shopping' group
$shoppingDetails = $cart->getGroupDetails('shopping');
```

## Working with Details Collection
Like the `getDetails()` method of cart instance, the above method allows you to have a complete overview of all information of the cart group.

By default, this Details Collection always has the following keys:

- `type`
- `name`
- `items_count`
- `quantities_sum`
- `subsections`
- `extra_info`

If this group contains any commercial carts, this Details Collection will have an additional key named `total`.

And if one of the carts of group has activated the built-in taxing system, this Details Collection will have the additional keys named `subtotal` and `tax_amont`.
