# Laravel Cart

A small package use to create cart (such as shopping, wishlist, recent views...) in Laravel application.

# Overview
Look at one of the following topics to learn more about Laravel Cart

* [Installation](#installation)
* [Usage](#usage)
* [Collections](#collections)
* [Instances](#instances)
* [Models](#models)
* [Exceptions](#exceptions)
* [Events](#events)

# Installation

You can install this package through [Composer](https://getcomposer.org).

- First, edit your project's `composer.json` file to require `jackiedo/cart`:

```php
...
"require": {
    ...
    "jackiedo/cart": "1.*"
},
```

- Next, update Composer from the Terminal:

```shell
$ composer update
```

- Once update operation completes, the third step is add the service provider. Open `app/config/app.php`, and add a new item to the providers array:

```php
...
'providers' => array(
    ...
    'Jackiedo\Cart\CartServiceProvider',
),
```

And the final step is add the follow line to the section `aliases`:

```php
'aliases' => array(
    ...
    'Cart' => 'Jackiedo\Cart\Facades\Cart',
),
```

# Usage

### Add item to cart

Add a new item.

```php
/**
 * Add a new item to the cart
 *
 * @param  string|int  $id       ID of the item (such as product's id)
 * @param  string      $title    Name of the item
 * @param  int         $qty      Item qty to add to the cart
 * @param  float       $price    Price of one item
 * @param  array       $options  Array of additional options, such as 'size' or 'color'
 * @return Jackiedo\Cart\CartItem|null
 */
Cart::add( $id, $title, $quantity, $price [, $options = array()] );
```

**example:**

```php
$row = Cart::add(37, 'Item Title', 5, 100.00, ['color' => 'red', 'size' => 'M']);

// Collection CartItem: {
//    rawId    : '8a48aa7c8e5202841ddaf767bb4d10da'
//    id       : 37
//    title    : 'Item Title'
//    qty      : 5
//    price    : 100.00
//    subtotal : 500.00
//    options  : Collection CartItemOptions : {
//                   color : 'red'
//                   size  : 'M'
//               }
// }

$rawId = $row->rawId(); // get rawId (8a48aa7c8e5202841ddaf767bb4d10da)
$rowQty = $row->qty;    // 5
...
```

### Update item

Update the specified item.

```php
/**
 * Update the quantity of one row of the cart
 *
 * @param  string     $rawId      The rawId of the item you want to update
 * @param  int|array  $attribute  New quantity of the item|Array of attributes to update
 * @return Jackiedo\Cart\CartItem
 */
Cart::update(string $rawId, int $quantity);
Cart::update(string $rawId, array $arrtibutes);
```

**example:**

```php
$rawId = '8a48aa7c8e5202841ddaf767bb4d10da';

// Update title and options
$row = Cart::update($rawId, ['title' => 'New item name', 'options' => ['color' => 'yellow']]);

// or only update quantity
$row = Cart::update($rawId, 5);
```

### Get all items

Get all the items.

```php
/**
 * Get the cart content
 *
 * @return \Illuminate\Support\Collection
 */
Cart::all();
// or use alias
Cart::content();
```

**example:**

```php
$items = Cart::content();

// Collection $items: {
//     8a48aa7c8e5202841ddaf767bb4d10da: {
//         rawId: '8a48aa7c8e5202841ddaf767bb4d10da',
//         id: 37,
//         title: 'New item name',
//         qty: 5,
//         price: 100.00,
//         subtotal: 500.00,
//         options: {
//             'color': 'yellow',
//             'size': 'M'
//         }
//     },
//     4c48ajh68e5202841ed52767bb4d10fc: {
//         rawId: '4c48ajh68e5202841ed52767bb4d10fc',
//         id: 42,
//         title: 'Men T-Shirt Apolo',
//         qty: 1,
//         price: 1000.00,
//         subtotal: 1000.00,
//         options: {
//             'color': 'red',
//             'size': 'L'
//         }
//     }
//     ...
// }
```


### Get item

Get the specified item.

```php
/**
 * Get a row of the cart by its unique ID
 *
 * @param  string  $rawId  The ID of the row to fetch
 * @return Jackiedo\Cart\CartItem
 */
Cart::get(string $rawId);
```

**example:**

```php
$item = Cart::get('8a48aa7c8e5202841ddaf767bb4d10da');

// Collection $item: {
//    rawId    : '8a48aa7c8e5202841ddaf767bb4d10da'
//    id       : 37
//    title    : 'Item Title'
//    qty      : 5
//    price    : 100.00
//    subtotal : 500.00
//    options  : {
//        'color'   : 'red',
//        'size'    : 'M'
//    }
// }
```

### Remove item

Remove the specified item by raw ID.

```php
/**
 * Remove a row from the cart
 *
 * @param  string  $rawId  The unique ID of the item
 * @return boolean
 */
Cart::remove(string $rawId);
```

**example:**

```php
Cart::remove('8a48aa7c8e5202841ddaf767bb4d10da');
```

### Destroy cart

Clean Shopping Cart.

```php
/**
 * Empty the cart
 *
 * @return boolean
 */
Cart::destroy();
```

**example:**

```php
Cart::destroy();
```

### Get total price

Returns the total price of all items.

```php
/**
 * Get the price total
 *
 * @return float
 */
Cart::total();
```

**example:**

```php
$total = Cart::total();
```


### Count rows

Return the number of rows.

```php
/**
 * Get the number of items in the cart
 *
 * @param  boolean  $totalItems  Get all the items (when false, will return the number of rows)
 * @return int
 */
Cart::count(false);
// or use alias
Cart::countRows();
```

**example:**

```php
Cart::add(37, 'Item name', 5, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(37, 'Item name', 1, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(37, 'Item name', 5, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(127, 'foobar', 15, 100.00, ['color' => 'green', 'size' => 'S']);
$rows = Cart::countRows(); // 2
```


### Count quantity

Returns the quantity of all items

```php
/**
 * Get the number of items in the cart
 *
 * @param  boolean  $totalItems  Get all the items (when false, will return the number of rows)
 * @return int
 */
Cart::count($totalItems = true);
```

`$totalItems` : When `false`,will return the number of rows.

**example:**

```php
Cart::add(37, 'Item name', 5, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(37, 'Item name', 1, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(37, 'Item name', 5, 100.00, ['color' => 'red', 'size' => 'M']);
$count = Cart::count(); // 11 (5+1+5)
```

### Search items

Search items by property.

```php
/**
 * Search if the cart has a item
 *
 * @param  array  $search  An array with the item ID and optional options
 * @return Illuminate\Support\Collection;
 */
Cart::search(array $conditions);
```

**example:**

```php
$items = Cart::search(['options' => ['color' => 'red']]);
$items = Cart::search(['title' => 'Item name']);
$items = Cart::search(['id' => 10, 'options' => ['size' => 'L']]);
```

# Collections

As you might have seen, there are two methods and one property  both return a Collection. These are the `Cart::content() (Cart)`, `Cart::get() (CartItem)` and `Cart::get()->options (CartItemOptions)`.

These Collections extends the 'native' Laravel Collection class, so all methods you know from Collection class can also be used on your shopping cart. With some addition to easily work with your carts content.

# Instances

Now the packages also supports multiple instances of the cart. The way this works is like this:

You can set the current instance of the cart with `Cart::instance('newInstance')`, at that moment, the active instance of the cart is `newInstance`, so when you add, remove or get the content of the cart, you work with the `newInstance` instance of the cart.

If you want to switch instances, you just call `Cart::instance('otherInstance')` again, and you're working with the `otherInstance` again.

So a little example:

```php
Cart::instance('shopping')->add('37', 'Product 1', 1, 9.99);

// Get the content of the 'shopping' cart
Cart::content();

Cart::instance('wishlist')->add('42', 'Product 2', 1, 19.95, array('size' => 'medium'));

// Get the content of the 'wishlist' cart
Cart::content();

// If you want to get the content of the 'shopping' cart again...
Cart::instance('shopping')->content();

// And the count of the 'wishlist' cart again
Cart::instance('wishlist')->count();
```

**Note:**
- Keep in mind that the cart stays in the last set instance for as long as you don't set a different one during script execution.

- The default cart instance is called `main`, so when you're not using instances,`Cart::content();` is the same as `Cart::instance('main')->content()`

# Models

A special feature is associating a model with the items in the cart. Let's say you have a `Product` model in your application. With the new `associate()` method, you can tell the cart that an item in the cart, is associated to the `Product` model. That way you can access your model right from the `CartItem`!

```php
/**
 * Set the associated model
 *
 * @param  string  $modelName       The name of the model
 * @param  string  $modelNamespace  The namespace of the model
 * @return Jackiedo\Cart\Cart
 */
Cart::associate(string $modelName, string $modelNamespace = null);
```

**example:**

```php
Cart::associate('ShoppingProduct', 'App\Models');
$item = Cart::get('8a48aa7c8e5202841ddaf767bb4d10da');
$item->shopping_product->title; // $item->shopping_product is instance of 'App\Models\ShoppingProduct'
```

The keyword to access the model is the snake case of model name you associated (Ex: Model name is ShoppingProduct, then the keyword is shopping_product).
The `associate()` method has a second optional parameter for specifying the model namespace.

# Exceptions

The Cart package will throw exceptions if something goes wrong. This way it's easier to debug your code using the Cart package or to handle the error based on the type of exceptions. The Cart packages can throw the following exceptions:

| Exception                     | Reason                                                               |
| ----------------------------- | -------------------------------------------------------------------- |
| *CartInvalidItemException*    | When a new product misses id or title argument (`id`, `title`)       |
| *CartInvalidPriceException*   | When a non-numeric or negative price is passed                       |
| *CartInvalidQtyException*     | When a non-numeric or less than 1 quantity is passed                 |
| *CartInvalidRawIDException*   | When the `$rawId` that got passed doesn't exists in the current cart |
| *CartUnknownModelException*   | When an unknown model is associated to a cart row                    |

# Events

| Event Name        | Parameters            |
| ----------------- | --------------------- |
| *cart.adding*     | ($attributes, $cart); |
| *cart.added*      | ($attributes, $cart); |
| *cart.updating*   | ($row, $cart);        |
| *cart.updated*    | ($row, $cart);        |
| *cart.removing*   | ($row, $cart);        |
| *cart.removed*    | ($row, $cart);        |
| *cart.destroying* | ($cart);              |
| *cart.destroyed*  | ($cart);              |

You can easily handle these events, for example:

```php
Event::on('cart.adding', function($attributes, $cart){
    // code
});
```

# License

MIT

# Thanks for use
Hopefully, this package is useful to you.