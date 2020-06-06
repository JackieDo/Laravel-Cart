# Retrieve applied actions
## Get the specific action
To retrieve an applied action, you need to have that action's hash code (the `hash` attribute).

**Method syntax:**

```php
/**
 * Get an action in the actions container
 *
 * @param  string $actionHash The unique identifier of the action
 *
 * @return Jackiedo\Cart\Action
 */
public function getAction($actionHash);
```

## Get many specific actions
You can retrieve all or some of the applied actions using the following syntax:

**Method syntax:**

```php
/**
 * Get all actions in the actions container that match the given filter
 *
 * @param  mixed   $filter    Search filter
 * @param  boolean $complyAll Indicates that the results returned must satisfy
 *                            all the conditions of the filter at the same time
 *                            or that only parts of the filter.
 *
 * @return array
 */
public function getActions($filter = null, $complyAll = true)
```

**Note:**

- The `$filter` parameter in the above method can be one of the following data types:
    + `null`
    + An `array` of hash codes
    + An `array` of attributes
    + A `closure` is treated as a custom filter
- In the case of the `$filter` parameter is an array of attributes, the `$complyAll` parameter will be used to indicates that the results returned must satisfy all the conditions of the filter at the same time or that only parts of the filter.
- More references [here](usage/items/retrieve-added-items#get-many-specific-items).
