# Other methods to work with items
## For item instance
#### Retrieve the cart instance that item belongs to.
**Method syntax:**

```php
/**
 * Get the cart instance that this item belongs to
 *
 * @return Jackiedo\Cart\Cart
 */
public function getCart();
```

#### Get value of one or some extended information
**Method syntax:**

```php
/**
 * Get value of one or some extended informations of the current item
 * using "dot" notation.
 *
 * @param  null|string|array $information The information want to get
 * @param  mixed             $default
 *
 * @return mixed
 */
public function getExtraInfo($information = null, $default = null)
```

**Example:**

```php
$item = $cart->addItem([
    // ...
]);

return $item->getExtraInfo('date_time.added_at');
```

#### Get value of one or some options
**Method syntax:**

```php
/**
 * Get value of one or some options of the item
 * using "dot" notation.
 *
 * @param  null|string|array $options The option want to get
 * @param  mixed             $default
 *
 * @return mixed
 */
public function getOptions($options = null, $default = null);
```

**Example:**

```php
$item = $cart->addItem([
    // ...
]);

return $item->getOptions('size.label');
```

#### Check whether an item is taxable or not
**Method syntax:**

```php
/**
 * Determines whether this is a taxable item
 * This is alias of the getTaxable() method
 *
 * @return boolean
 */
public function isTaxable();
```

## For cart instance
#### Check for the existence of a tax
**Method syntax:**

```php
/**
 * Determines if the item exists in the cart
 *
 * @param  string $itemHash The unique identifier of the item
 *
 * @return boolean
 */
public function hasItem($itemHash);
```

#### Count the number of added items
**Method syntax:**

```php
/**
 * Count the number of items in the cart that match the given filter
 *
 * @param  mixed   $filter    Search filter
 * @param  boolean $complyAll Indicates that the results returned must satisfy
 *                            all the conditions of the filter at the same time
 *                            or that only parts of the filter.
 *
 * @return integer
 */
public function countItems($filter = null, $complyAll = true);
```

**Note:** The `$filter` and `$complyAll` parameters are used in the same way as the `getItems()` method.

#### Sum the quantities of items in the cart
**Method syntax:**

```php
/**
 * Sum the quantities of all items in the cart that match the given filter
 *
 * @param  mixed   $filter    Search filter
 * @param  boolean $complyAll Indicates that the results returned must satisfy
 *                            all the conditions of the filter at the same time
 *                            or that only parts of the filter.
 *
 * @return integer
 */
public function sumItemsQuantity($filter = null, $complyAll = true);
```

**Note:** The `$filter` and `$complyAll` parameters are used in the same way as the `getItems()` method.
