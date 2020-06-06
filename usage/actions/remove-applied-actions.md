# Remove applied actions
## Remove the specific action
To remove an applied action, you need to have that action's hash code (the `hash` attribute).

**Method syntax:**

```php
/**
 * Remove an action from the actions container
 *
 * @param  string  $actionHash The unique identifier of the action
 * @param  boolean $withEvent  Enable firing the event
 *
 * @return $this
 */
public function removeAction($actionHash, $withEvent = true);
```

## Remove all actions
**Method syntax:**

```php
/**
 * Remove all actions from the actions container
 *
 * @param  boolean $withEvent Enable firing the event
 *
 * @return $this
 */
public function clearActions($withEvent = true);
```
