# Remove applied taxes
## Remove the specific tax
To remove an applied tax, you need to have that tax's hash code (the `hash` attribute).

**Method syntax:**

```php
/**
 * Remove an applied tax from the cart
 *
 * @param  string  $taxHash   The unique identifier of the tax instance
 * @param  boolean $withEvent Enable firing the event
 *
 * @return $this
 */
public function removeTax($taxHash, $withEvent = true);
```

## Remove all taxes
**Method syntax:**

```php
/**
 * Remove all apllied taxes from the cart
 *
 * @param  boolean $withEvent Enable firing the event
 *
 * @return $this
 */
public function clearTaxes($withEvent = true);
```
