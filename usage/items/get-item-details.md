# Get item details as Laravel Collection
**Method syntax:**

```php
/**
 * Get details of the item as a collection
 *
 * @param  boolean $withActions Include details of applied actions in the result
 *
 * @return Jackiedo\Cart\Details
 */
public function getDetails($withActions = true);
```

**Example:**

```php
$shoppingCart = Cart::name('shopping')->useForCommercial();
$recentViewed = Cart::newInstance('recently_viewed')->useForCommercial(false);

$addedProduct = $shoppingCart->addItem([
    // ...
]);

$recentArticle = $recentViewed->addItem([
    // ...
]);

// Return details of $addedProduct
return $addedProduct->getDetails();

// Return details of $recentArticle
return $recentArticle->getDetails();
```

## Working with Details Collection
Like the `getDetails()` method of cart instance, the above method allows you to have a complete overview of all information of the added item. However, the number of keys in this Details Collection is not the same for all items, it depends on whether this item belongs to a commercial cart or not.

By default, this Details Collection always has the following keys:

- `hash`
- `associated_class`
- `id`
- `title`
- `extra_info`

And if this item belongs to is a commercial cart, this Details Collection will have additional keys:

- `quantity`
- `price`
- `taxable`
- `total_price`
- `actions_count`
- `actions_amount`
- `subtotal`
- `options`
- `applied_actions`
