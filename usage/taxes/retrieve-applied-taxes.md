# Retrieve applied taxes
## Get the specific tax
To retrieve an applied tax, you need to have that tax's hash code (the `hash` attribute).

**Method syntax:**

```php
/**
 * Get an applied tax in the cart
 *
 * @param  string $taxHash The unique identifire of the tax instance
 *
 * @return Jackiedo\Cart\Tax
 */
public function getTax($taxHash);
```

## Get many specific taxes
You can retrieve all or some of the applied taxes in the cart using the following syntax:

**Method syntax:**

```php
/**
 * Get all tax of this cart that match the given filter
 *
 * @param  mixed   $filter    Search filter
 * @param  boolean $complyAll Indicates that the results returned must satisfy
 *                            all the conditions of the filter at the same time
 *                            or that only parts of the filter.
 *
 * @return array
 */
public function getTaxes($filter = null, $complyAll = true);
```

**Note:**

- The `$filter` parameter in the above method can be one of the following data types:
    + `null`
    + An `array` of hash codes
    + An `array` of attributes
    + A `closure` is treated as a custom filter
- In the case of the `$filter` parameter is an array of attributes, the `$complyAll` parameter will be used to indicates that the results returned must satisfy all the conditions of the filter at the same time or that only parts of the filter.
- More references [here](usage/items/retrieve-added-items#get-many-specific-items).
