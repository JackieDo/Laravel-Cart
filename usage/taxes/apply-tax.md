# Apply a tax to the cart
**Method syntax:**

```php
/**
 * Add a tax into the taxes container of this cart
 *
 * @param  array   $attributes The tax attributes
 * @param  boolean $withEvent  Enable firing the event
 *
 * @return Jackiedo\Cart\Tax|null
 */
public function applyTax(array $attributes = [], $withEvent = true)
```

The result of this method is an instance of the `Jackiedo\Cart\Tax` class. However, you cannot instantiate an object from this class and treat it as an applied tax. A properly applied tax can only be obtained via the above `applyTax()` method. You need to pass this method an array of the following attributes:

* `id`:
    - Description: Raw id of the tax, such as information from the id field in the database.
    - Type: string | int
    - Required: true
* `title`:
    - Description: The short title of the tax.
    - Type: string
    - Required: true
* `rate`:
    - Description: The tax rate.
    - Type: float
    - Required: true
    - Default: The `default_tax_rate` setting in the configuration file (see [here](configuration#default-tax-rate))
* `extra_info`:
    - Description: Store other extended information.
    - Type: array
    - Required: false
    - Default: []

**Example:**

```php
$cart = Cart::name('shopping');

$tax = $cart->applyTax([
    'id'         => 123,
    'title'      => 'VAT 10%',
    'rate'       => 10,
    'extra_info' => [
        'description'    => 'The V.A.T tax',
        'reference_link' => 'https://example.com'
    ]
]);
```

## The attributes of tax
An applied tax contains the attributes that you passed into the `applyTax()` method and has some other special attributes:

* `hash`:
    - Description: The unique identifier of tax in the cart.
    - Type: string
* `amount`:
    - Description: The calcualted tax amount for the cart.
    - Type: float

**Note:** The `hash` attribute is used to identify the different taxes in the cart. This information is made up of the `id` attribute.

## Retrieve tax attributes
You can access the attributes of the applied tax the same way you access the attributes of an item. More references [here](usage/items/add-item#retrieve-item-attributes).
