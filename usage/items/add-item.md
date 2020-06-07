# Add an item to the cart
**Method syntax:**

```php
/**
 * Add an item to the cart
 *
 * @param  array   $attributes The item attributes
 * @param  boolean $withEvent  Enable firing the event
 *
 * @return Jackiedo\Cart\Item|null
 */
public function addItem(array $attributes = [], $withEvent = true)
```

The result of this method is an instance of the `Jackiedo\Cart\Item` class. However, you cannot instantiate an object from this class and treat it as an added item. A properly added item can only be obtained via the above `addItem()` method. You need to pass this method an array of the following attributes:

* `id`:
    - Description: Raw id of the item, such as information from the id field in the database.
    - Type: string | int
    - Required: true
* `title`:
    - Description: The name of the item.
    - Type: string
    - Required: true
* `quantity`:
    - Description: Number of items to add to the cart.
    - Type: int
    - Required: false
    - Default: 1
* `price`:
    - Description: The price of the item.
    - Type: float
    - Required: false
    - Default: 0
* `options`:
    - Description: Optional item information, such as size, color...
    - Type: array
    - Required: false
    - Default: []
* `taxable`:
    - Description: Specifies whether this item is taxable or not (will be explained later in advanced usage).
    - Type: boolean
    - Required: false
    - Default: true
* `extra_info`:
    - Description: Store other extended information.
    - Type: array
    - Required: false
    - Default: []

**Example:**

```php
$shoppingCart   = Cart::name('shopping');
$recentlyViewed = Cart::newInstance('recently_viewed')->useForCommercial(false);

$productItem  = $shoppingCart->addItem([
    'id'       => 37,
    'title'    => 'Polo T-shirt for men',
    'quantity' => 2,
    'price'    => 100,
    'options' => [
        'size' => [
            'label' => 'XL',
            'value' => 'XL'
        ],
        'color' => [
            'label' => 'Red',
            'value' => '#f00'
        ]
    ],
    'extra_info' => [
        'date_time' => [
            'added_at' => time(),
        ]
    ]
]);

$recentArticle  = $recentlyViewed->addItem([
    'id'    => 2,
    'title' => 'Demo artile on the blog',
]);
```

## The attributes of item
An added item contains the attributes that you passed into the `addItem()` method and has some other special attributes:

* `hash`:
    - Description: The unique identifier of item in the cart.
    - Type: string
* `associated_class`:
    - Description: The class name of associated model (will be explained later in advanced usage).
    - Type: string
* `total_price`:
    - Description: The total price of item, calculated by the product of `quantity` and price `attributes`.
    - Type: float
* `actions_count`:
    - Description: The number of applied actions for this item (will be explained later in advanced usage).
    - Type: int
* `actions_amount`:
    - Description: The amount of applied actions (will be explained later in advanced usage).
    - Type: float
* `subtotal`:
    - Description: The subtotal amount of item, calculated by the sum of `total_price` and `actions_amount` attrbutes.
    - Type: float
* `taxable_amount`:
    - Description: The amount will be taxable (will be explained later in advanced usage).
    - Type: float

**Note:** The `hash` attribute is used to identify the different items in the cart. This information is made up of the `id`, `price`, and `options` attributes. This means that you can add the same item with same `id`, but with different `prices` or `options` attributes.

## Retrieve item attributes
Each attribute of an added item can be accessed in two ways:

### Using the `get()` method
**Method syntax:**

```php
/**
 * Get value of attribute
 *
 * @param  string $attribute The attribute
 * @param  mixed  $default   The return value if attribute does not exist
 *
 * @return mixed
 */
public function get($attribute, $default = null);
```

**Example:**

```php
$cartItem = Cart::addItem([...]);

$hashCode  = $cartItem->get('hash');
$title     = $cartItem->get('title');
$quantity  = $cartItem->get('quantity');
$extraInfo = $cartItem->get('extra_info');
```

### Using the corresponding getter
The attributes of an added item can also be accessed using a corresponding getter with the syntax `getAttributeNameInCamelCase()`.

**Example:**

```php
$cartItem = Cart::addItem([...]);

$hashCode  = $cartItem->getHash();
$title     = $cartItem->getTitle();
$quantity  = $cartItem->getQuantity();
$extraInfo = $cartItem->getExtraInfo();
```
