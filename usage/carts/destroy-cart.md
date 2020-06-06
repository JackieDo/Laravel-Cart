# Destroy the cart
Usually this is not necessary. But if at some point you need to delete a cart including added items, applied actions and taxes, you can do this easily using the `destroy()` method with the following syntax:

**Method syntax:**

```php
/**
 * Remove cart from session
 *
 * @param  boolean $withEvent Enable firing the event
 *
 * @return boolean
 */
public function destroy($withEvent = true);
```

This method will remove the current cart from session including added items, applied taxes, applied actions, extended information and configurations of the cart.
