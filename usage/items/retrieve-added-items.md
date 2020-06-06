# Retrieve added items
## Get the specific item
To retrieve an added item, you need to have that item's hash code (the `hash` attribute).

**Method syntax:**

```php
/**
 * Retrieve an item in the cart
 *
 * @param  string $itemHash The unique identifier of the item
 *
 * @return Jackiedo\Cart\Item
 */
public function getItem($itemHash);
```

## Get many specific items
You can retrieve all or some of the added items in the cart using the following syntax:

**Method syntax:**

```php
/**
 * Get all items in the cart that match the given filter
 *
 * @param  mixed   $filter    Search filter
 * @param  boolean $complyAll Indicates that the results returned must satisfy
 *                            all the conditions of the filter at the same time
 *                            or that only parts of the filter.
 *
 * @return array
 */
public function getItems($filter = null, $complyAll = true);
```

**Note:**

- The `$filter` parameter in the above method can be one of the following data types:
    + `null`
    + An `array` of hash codes
    + An `array` of attributes
    + A `closure` is treated as a custom filter
- In the case of the `$filter` parameter is an array of attributes, the `$complyAll` parameter will be used to indicates that the results returned must satisfy all the conditions of the filter at the same time or that only parts of the filter.

**Example 1 - Get all items:**

```php
// Get all items
$items = Cart::name('shopping')->getItems();

// Loop over all items and get item options
foreach ($items as $hash => $item) {
    print_r('<pre>');
    var_dump($item->getOptions());
    print_r('</pre>');
}
```

**Example 2 - Get some items using a set of hashes:**

```php
$matchedItems = Cart::name('shopping')->getItems([
    'item_f463238b660cac7006f7d775350e8360',
    'item_sf7897jkhdwGbsfg67456cfgc7006f7d'
]);
```

**Example 3 - Get some items using a set of attributes:**

```php
// Get all cart items that have color is red
$items = Cart::name('shopping')->getItems([
    'options' => [
        'color' => 'red'
    ]
]);

// Get all cart items that have title is [Polo T-shirt for men] AND size is [L]
$items = Cart::name('shopping')->getItems([
    'title'   => 'Polo T-shirt for men',
    'options' => [
        'size' => 'L'
    ]
]);

// Get all cart items that have id is [10], OR size is [M]
$items = Cart::name('shopping')->getItems([
    'id'      => 10,
    'options' => [
        'size' => 'M'
    ]
], false);
```

**Example 4 - Get some items using custom filter:**

```php
// Get all cart items that have the quantity attribute greater than or equal to 10
$items = Cart::name('shopping')->getItems(function($item) {
    return $item->getQuantity() >= 10;
});
```
