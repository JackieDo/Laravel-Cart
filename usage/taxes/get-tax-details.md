# Get tax details as Laravel Collection
**Method syntax:**

```php
/**
 * Get details of the applied tax as a collection
 *
 * @return Jackiedo\Cart\Details
 */
public function getDetails();
```

## Working with Details Collection
Like the `getDetails()` method of cart instance, the above method allows you to have a complete overview of all information of the applied tax.

The result of this method is an instance of `Jackiedo\Cart\Details` class and always has the following keys:

- hash
- id
- title
- rate
- amount
- extra_info
