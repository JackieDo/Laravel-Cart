# Remove added item
## Remove the specific item
To remove an added item, you need to have that item's hash code (the `hash` attribute).

**Method syntax:**

```php
/**
 * Remove an item from the cart
 *
 * @param  string  $itemHash  The unique identifier of the item
 * @param  boolean $withEvent Enable firing the event
 *
 * @return Jackiedo\Cart\Cart
 */
public function removeItem($itemHash, $withEvent = true);
```

## Remove all items
**Method syntax:**

```php
/**
 * Delete all items in the items container
 *
 * @param  boolean $withEvent Enable firing the event
 *
 * @return Jackiedo\Cart\Cart
 */
public function clearItems($withEvent = true);
```
