# Get action details as Laravel Collection
**Method syntax:**

```php
/**
 * Get details of the action as a collection
 *
 * @return Jackiedo\Cart\Details
 */
public function getDetails();
```

## Working with Details Collection
Like the `getDetails()` method of cart instance, the above method allows you to have a complete overview of all information of the applied action.

The result of this method is an instance of `Jackiedo\Cart\Details` class and always has the following keys:

- hash
- group
- id
- title
- target
- value
- rules
- enabled
- amount
- extra_info

And, if the cart that this action belongs to enabled built-in taxing system, the result will have the `taxable` key.
