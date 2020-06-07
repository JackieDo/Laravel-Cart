# Store more extended information for the cart group
## Set extended information
**Method syntax:**

```php
/**
 * Set value for one or some extended informations of the group
 * using "dot" notation.
 *
 * @param  string       $groupName   The name of the cart group
 * @param  string|array $information The information want to set
 * @param  mixed        $value       The value of information
 *
 * @return $this
 */
public function setGroupExtraInfo($groupName, $information, $value = null);
```

## Get extended information
**Method syntax:**

```php
/**
 * Get value of one or some extended informations of the group
 * using "dot" notation
 *
 * @param  string            $groupName   The name of the cart group
 * @param  null|string|array $information The information want to get
 * @param  mixed             $default     The return value if information does not exist
 *
 * @return mixed
 */
public function getGroupExtraInfo($groupName, $information = null, $default = null);
```

## Remove extended information
**Method syntax:**

```php
/**
 * Remove one or some extended informations of the group
 * using "dot" notation
 *
 * @param  string            $groupName   The name of the cart group
 * @param  null|string|array $information The information want to remove
 *
 * @return $this
 */
public function removeGroupExtraInfo($groupName, $information = null);
```
