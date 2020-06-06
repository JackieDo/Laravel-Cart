# Store more extended information for the cart
## Why is it necessary?
In addition to information related to items, actions, taxes, sometimes we need to store other information such as information about delivery... The Laravel Cart allows us to do this easily.

## Set extended information
**Method syntax:**

```php
/**
 * Set value for one or some extended informations of the cart
 * using "dot" notation
 *
 * @param  string|array $information The information want to set
 * @param  mixed        $value       The value of information
 *
 * @return $this
 */
public function setExtraInfo($information, $value = null);
```

This method works quite similar to Laravel's `Arr::set()` method. The thing is, it returns the current cart. So you can call it continuously. Example:

```php
$cart = Cart::name('shopping');

$cart->setExtraInfo('shipping.address', '123 ABCD Street, Dist DEF...');
$cart->setExtraInfo('shipping.receiver_name', 'Jackie Do')->setExtraInfo('another', 'example');

// Or
$cart->setExtraInfo([
    'shipping.address'       => '123, Magna commodo fugiat eu consequat...',
    'shipping.receiver_name' => 'Jackie Do',
    'shipping.receive_hour'  => 1234567
]);

// Or
$cart->setExtraInfo([
    'shipping' => [
        'address'       => '123 ABCD Street, Dist DEF...',
        'receiver_name' => 'Jackie Do',
        'receive_hour'  => 1234567
    ]
]);
```

## Get extended information
**Method syntax:**

```php
/**
 * Get value of one or some extended informations of the cart
 * using "dot" notation
 *
 * @param  null|string|array $information The information want to get
 * @param  mixed             $default     The return value if information does not exist
 *
 * @return mixed
 */
public function getExtraInfo($information = null, $default = null);
```

**Example:**

```php
$shippingAddress = $cart->getExtraInfo('shipping.address');

// Or get all information about shipping
$shippingInfo = $cart->getExtraInfo('shipping');

// Or get all extra information
$extraInfo = $cart->getExtraInfo();
```

## Remove extended information
**Method syntax:**

```php
/**
 * Remove one or some extended informations of the cart
 * using "dot" notation
 *
 * @param  null|string|array $information The information want to remove
 *
 * @return $this
 */
public function removeExtraInfo($information = null);
```

**Example:**

```php
// Remove one specific information
$cart->removeExtraInfo('shipping.receiver_name');

// Or remove some specific information
$cart->removeExtraInfo([
    'information_1',
    'information_2',
]);

// Or remove all information
$cart->removeExtraInfo();
```
