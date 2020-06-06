# Other methods to work with taxes
## For tax instance
### Retrieve the cart instance that tax applied to.
**Method syntax:**

```php
/**
 * Get the cart instance that this tax applied to
 *
 * @return Jackiedo\Cart\Cart
 */
public function getCart();
```

### Get value of one or some extended information
**Method syntax:**

```php
/**
 * Get value of one or some extended informations of the current tax
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
$tax = $cart->applyTax([
    // ...
]);

return $tax->getExtraInfo('date_time.added_at');
```

## For cart instance
### Check for the existence of a tax
**Method syntax:**

```php
/**
 * Determines if the tax exists in the cart
 *
 * @param  string $taxHash The unique identifier of the tax
 *
 * @return boolean
 */
public function hasTax($taxHash);
```

### Count the number of applied taxes
**Method syntax:**

```php
/**
 * Count all taxes in the cart that match the given filter
 *
 * @param  mixed   $filter    Search filter
 * @param  boolean $complyAll Indicates that the results returned must satisfy
 *                            all the conditions of the filter at the same time
 *                            or that only parts of the filter.
 *
 * @return integer
 */
public function countTaxes($filter = null, $complyAll = true);
```

**Note:** The `$filter` and `$complyAll` parameters are used in the same way as the `getItems()` method.

### Get total taxable amount
**Method syntax:**

```php
/**
 * Calculate total taxable amounts include the taxable amount of cart and all items
 *
 * @return float
 */
public function getTaxableAmount();
```

**Note:** More references [here](usage/taxes/built-in-taxing-system#how-the-taxing-system-works).

### Get total tax rate
**Method syntax:**

```php
/**
 * Get the total tax rate applied to the current cart.
 *
 * @return float
 */
public function getTaxRate();
```

### Get total tax amount
**Method syntax:**

```php
/**
 * Get the total tax amount applied to the current cart.
 *
 * @return float
 */
public function getTaxAmount();
```

**Note:** More references [here](usage/taxes/built-in-taxing-system#how-the-taxing-system-works).
