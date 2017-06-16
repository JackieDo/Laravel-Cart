# Laravel Cart
[![Latest Stable Version](https://poser.pugx.org/jackiedo/cart/v/stable)](https://packagist.org/packages/jackiedo/cart)
[![Total Downloads](https://poser.pugx.org/jackiedo/cart/downloads)](https://packagist.org/packages/jackiedo/cart)
[![Latest Unstable Version](https://poser.pugx.org/jackiedo/cart/v/unstable)](https://packagist.org/packages/jackiedo/cart)
[![License](https://poser.pugx.org/jackiedo/cart/license)](https://packagist.org/packages/jackiedo/cart)

A small package use to create cart (such as shopping, wishlist, recent views...) in Laravel application.

# Overview
Look at one of the following topics to learn more about Laravel Cart

* [Versions and compatibility](#versions-and-compatibility)
* [Installation](#installation)
* [Basic usage](#basic-usage)
    - [The Cart facade](#the-cart-facade)
    - [Named your cart instances](#named-your-cart-instances)
    - [Get current cart instance](#get-current-cart-instance)
    - [Add an item to cart](#add-an-item-to-cart)
    - [Update cart item](#update-cart-item)
    - [Get the specified cart item](#get-the-specified-cart-item)
    - [Get all cart items](#get-all-cart-items)
    - [Get total price of all cart items](#get-total-price-of-all-cart-items)
    - [Count cart items](#count-cart-items)
    - [Count quantities of all cart items](#count-quantities-of-all-cart-items)
    - [Search cart items](#search-cart-items)
    - [Remove the specified cart item](#remove-the-specified-cart-item)
    - [Destroy your cart](#destroy-your-cart)
* [Advanced usage](#advanced-usage)
    - [Collections](#collections)
    - [Eloquent model association](#eloquent-model-association)
    - [Custom search building](#custom-search-building)
    - [Dependency injection](#dependency-injection)
    - [Event and listener](#event-and-listener)
    - [Exceptions](#exceptions)
* [License](#license)

## Versions and compatibility
Currently, Laravel Cart have two branchs compatible with Laravel 4.x and 5.x as follow:

| Branch                                                     | Laravel version  |
| ---------------------------------------------------------- | ---------------- |
| [v1.0](https://github.com/JackieDo/Laravel-Cart/tree/v1.0) | 4.x              |
| [v2.0](https://github.com/JackieDo/Laravel-Cart/tree/v2.0) | 5.x              |

> This documentation is use for Laravel 5.x

## Installation
You can install this package through [Composer](https://getcomposer.org).

- First, edit your project's `composer.json` file to require `jackiedo/cart`:

```php
...
"require": {
    ...
    "jackiedo/cart": "2.*"
},
```

- Next step, we run Composer update commend from the Terminal on your project source directory:

```shell
$ composer update
```

- Once update operation completes, the third step is add the service provider. Open `config/app.php`, and add a new item to the section `providers`:

```php
...
'providers' => array(
    ...
    'Jackiedo\Cart\CartServiceProvider',
),
```

> Note: From Laravel 5.1, you should write as `Jackiedo\Cart\CartServiceProvider::class`

And the final step is add the following line to the section `aliases` in file `config/app.php`:

```php
'aliases' => array(
    ...
    'Cart' => 'Jackiedo\Cart\Facades\Cart',
),
```

> Note: From Laravel 5.1, you should write as `Jackiedo\Cart\Facades\Cart::class`

## Basic usage

### The Cart facade
Laravel Cart has a facade is stored at `Jackiedo\Cart\Facades\Cart`. You can do any cart operation through this facade.

### Named your cart instances
Laravel Cart supports multiple instances of the cart. This is useful for using different carts for different management purposes, such as shopping cart, whishlist items, recently views... The way this works is like this:

Whenever you create a shopping cart, you should name it so that it can be distinguished from other carts. You can set name for an instance of the cart by calling `Cart::instance($newInstance)`. From this point of time, the active instance of the cart will have a specified name, so whenever you perform a cart operation, you must specify a specific cart.

Example:

```php
Cart::instance('shopping');
```

If you want to switch instances, you just call `Cart::instance($otherInstance)` again, and you're working with the other instance.

So a little example:

```php
// Create new instance of the cart with named is `shopping`
$cart = Cart::instance('shopping');

// Perform an example method with this instance
$cart->doExampleMethod();

// Switch to  whishlist cart and do another method
$info = Cart::instance('wishlist')->doExampleMethod();

...
```

**Note:**
> Keep in mind that the cart stays in the last set instance for as long as you don't set a different one during script execution.

> The default cart instance is called `default`, so when you're not using instances, example `Cart::doExampleMethod();` is the same as `Cart::instance('default')->doExampleMethod();`

### Get current cart instance
You can easily get cuurent cart instance name by calling `Cart::getInstance()` method.

### Add an item to cart
Adding an item to the cart is really simple, you just use the add() method, which accepts a variety of parameters.

```php
/**
 * Add an item to the cart
 *
 * @param  string|int  $rawId    Associated model or Unique ID of item before insert to the cart
 * @param  string      $title    Name of item
 * @param  int         $qty      Quantities of item want to add to the cart
 * @param  float       $price    Price of one item
 * @param  array       $options  Array of additional options, such as 'size' or 'color'
 *
 * @return Jackiedo\Cart\CartItem
 */
Cart::add($rawId, $title[, int $qty = 1[, float $price = 0[, array $options = array()]]]);
```

Example:

```php
// Add an product to the cart
$shoppingCartItem = Cart::instance('shopping')->add(37, 'Polo T-shirt for men', 5, 20.00, ['color' => 'red', 'size' => 'M']);

// Collection CartItem: {
//    id       : '8a48aa7c8e5202841ddaf767bb4d10da'
//    raw_id   : 37
//    title    : 'Polo T-shirt for men'
//    qty      : 5
//    price    : 20.00
//    subtotal : 100.00
//    options  : Collection CartItemOptions : {
//                   color : 'red'
//                   size  : 'M'
//               }
// }

// Get id of cart item. This ID is used to distinguish items with different attributes in the cart.
$thisCartItemId = $shoppingCartItem->id;      // 8a48aa7c8e5202841ddaf767bb4d10da

// Get sub total price of cart item
$thisSubTotal = $shoppingCartItem->subtotal;  // 100.00

// Add an article to recently view
$recentView = Cart::instance('recent-view')->add(2, 'Example article');
...
```

> Cart item ID is used to distinguish items with different attributes in the cart. So with the same product, but when you add to the cart with different options, you will have different cart items with different IDs.

### Update cart item
Update the specified cart item with given quantity or attributes. To do this, you need a cart item ID.

```php
/**
 * Update an item in the cart with the given ID.
 *
 * @param  string     $cartItemId  ID of an item in the cart
 * @param  int|array  $attributes  New quantity of item or array of attributes to update
 *
 * @return Jackiedo\Cart\CartItem|null
 */
Cart::update(string $cartItemId, int $quantity);
Cart::update(string $cartItemId, array $attributes);
```

Example:

```php
$cartItemId = '8a48aa7c8e5202841ddaf767bb4d10da';

// Update title and options
$cartItem = Cart::instance('shopping')->update($cartItemId, ['title' => 'New item name', 'options' => ['color' => 'yellow']]);

// or only update quantity
$cartItem = Cart::instance('shopping')->update($cartItemId, 10);
```

> Note: You can only update attributes about title, quantity, price and options. Whenever you update cart item's info, the cart item's ID can be changed.

### Get the specified cart item
Get the specified cart item with given cart item's ID

```php
/**
 * Get an item in the cart by its ID.
 *
 * @param  string  $cartItemId  ID of an item in the cart
 *
 * @return Jackiedo\Cart\CartItem
 */
Cart::get($cartItemId);
```

Example:

```php
$item = Cart::instance('shopping')->get('8a48aa7c8e5202841ddaf767bb4d10da');

// Collection CartItem: {
//    id       : '8a48aa7c8e5202841ddaf767bb4d10da'
//    raw_id   : 37
//    title    : 'Polo T-shirt for men'
//    qty      : 5
//    price    : 20.00
//    subtotal : 100.00
//    options  : Collection CartItemOptions : {
//                   color : 'red'
//                   size  : 'M'
//               }
// }
```

### Get all cart items
Get all the cart items in the cart.

```php
/**
 * Get cart content
 *
 * @return \Illuminate\Support\Collection
 */
Cart::all();
```

Example:

```php
$items = Cart::instance('shopping')->all();

// Collection $items: {
//     8a48aa7c8e5202841ddaf767bb4d10da: {
//         id: '8a48aa7c8e5202841ddaf767bb4d10da',
//         raw_id: 37,
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
//         id: '4c48ajh68e5202841ed52767bb4d10fc',
//         raw_id: 42,
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

### Get total price of all cart items
Returns the total price of all items in the cart.

```php
/**
 * Get the total price of all items in the cart.
 *
 * @return float
 */
Cart::total();
```

Example:

```php
$total = Cart::instance('shopping')->total();
```

### Count cart items
Return the number of unique items in the cart.

```php
/**
 * Get number of items in the cart.
 *
 * @return int
 */
Cart::countItems();
```

Example:

```php
Cart::instance('shopping')->add(37, 'Polo T-shirt for men', 5, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(37, 'Polo T-shirt for men', 1, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(37, 'Polo T-shirt for men', 5, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(127, 'Another example product', 15, 100.00, ['color' => 'green', 'size' => 'S']);

$items = Cart::countItems();  // 2 (Polo T-shirt for men, Another example product)
```

### Count quantities of all cart items
Return the number of cart items.

```php
/**
 * Get quantities of all items in the cart.
 *
 * @return int
 */
Cart::countQuantities();
```

Example:

```php
Cart::instance('shopping')->add(37, 'Polo T-shirt for men', 5, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(37, 'Polo T-shirt for men', 1, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(37, 'Polo T-shirt for men', 5, 100.00, ['color' => 'red', 'size' => 'M']);
Cart::add(127, 'Another example product', 15, 100.00, ['color' => 'green', 'size' => 'S']);

$quantities = Cart::countQuantities();  // 26
```

### Search cart items
To search cart items in the cart, you must provide array of the cart item's attributes

```php
/**
 * Search if the cart has a item
 *
 * @param  Closure|array  $filter    A closure or an array with item's attributes
 * @param  boolean        $allScope  Determine that the filter is satisfied for all
 *                                   attributes simultaneously or in combination.
 *
 * @return Illuminate\Support\Collection;
 */
Cart::search($filter[, boolean $allScope = true]);
```

> Note: The `$allScope` parameter use to determine that the filter is satisfied for all attributes simultaneously or in combination.

Example:

```php
// Get all cart items that have color is red
$items = Cart::instance('shopping')->search([
    'options' => [
        'color' => 'red'
    ]
]);

// Get all cart items that have title is [Polo T-shirt for men] AND size is [L]
$items = Cart::instance('shopping')->search([
    'title'   => 'Polo T-shirt for men',
    'options' => [
        'size' => 'L'
    ]
]);

// Get all cart items that have raw_id is [10], OR size is [M]
$items = Cart::instance('shopping')->search([
    'raw_id'  => 10,
    'options' => [
        'size' => 'M'
    ]
], false);
```

### Remove the specified cart item
Remove the specified cart item by its ID.

```php
/**
 * Remove an item in the cart with the given ID out of the cart.
 *
 * @param  string  $cartItemId  ID of an item in the cart
 *
 * @return Jackiedo\Cart\Cart
 */
Cart::remove($cartItemId);
```

Example:

```php
Cart::instance('shopping')->remove('8a48aa7c8e5202841ddaf767bb4d10da');
```

### Destroy your cart
Clean a specified cart with given cart instance name.

```php
/**
 * Remove all items in the cart
 *
 * @return Jackiedo\Cart\Cart
 */
Cart::destroy()
```

Example:

```php
Cart::instance('shopping')->destroy();
```

## Advanced usage

### Collections
As you might have seen, all result of cart content (response by methods such as `Cart::all()`, `Cart::search()`), every cart item (response by methods such as `Cart::add()`, `Cart::get()`, `Cart::update()`) and cart item's options (is property of one cart item) are return a Laravel Collection, so all methods you can call on a Laravel Collection are also available on the them.

Example:

```php
$count = Cart::instance('shopping')->all()->count();
```

And another example (group by attribute):

```php
$all = Cart::instance('shopping')->all()->groupBy('title');
```

### Eloquent model association
A special feature of Laravel Cart is association an Eloquent model with the cart item in the cart. Let's say you have a `Product` model in your application. With this feature, you can tell the cart that an item in the cart, is associated to a instance `Product` model. That way, you can access your instance of model right from instance of `CartItem`!

To be ready for this, Laravel Cart has one interface (with namespace is `Jackiedo\Cart\Contracts\UseCartable`) and one trait (with namespace is `Jackiedo\Cart\Traits\CanUseCart`). The rest is you just apply for your Eloquent model.

#### Preparing for association:
```php
<?php namespace App;

use Illuminate\Database\Eloquent\Model;

use Jackiedo\Cart\Contracts\UseCartable; // Interface
use Jackiedo\Cart\Traits\CanUseCart;     // Trait

class Product extends Model implements UseCartable {
    use CanUseCart;

    ...
}

```

#### Make a association
If your model implemented the UseCartable interface and you uses your model to add the item to the cart, it will associate automatically.

```php
/**
 * Add an model to the cart and make association
 *
 * @param  string|int  $rawId    Associated model or Unique ID of item before insert to the cart
 * @param  string      $title    Name of item
 * @param  int         $qty      Quantities of item want to add to the cart
 * @param  float       $price    Price of one item
 * @param  array       $options  Array of additional options, such as 'size' or 'color'
 *
 * @return Jackiedo\Cart\CartItem
 */
Cart::add($model[, int $qty = 1[, array $options = array()]]);
```

Example:

```php
// Get your model
$product = Product::find(1);

// Add 5 items of this product to the cart
$cartItem = Cart::instance('shopping')->add($product, 5);

// Or you can do this by another way
$cartItem = $product->addToCart('shopping', 5);

// Collection CartItem: {
//    id         : "8a48aa7c8e5202841ddaf767bb4d10da"
//    raw_id     : 37
//    title      : "Polo T-shirt for men"
//    qty        : 5
//    price      : 20.00
//    subtotal   : 100.00
//    options    : Collection CartItemOptions : {
//                     color : "red"
//                     size  : "M"
//                 }
//    associated : "App\Product"
// }
```

Please, notice the associated attribute of the cart item. Do you see the value is `App\Product`? Wow! now you can be able to directly access your model from this cart item instance through `model` property. Example:

```php
return $cartItem->model->name; // Get name of your product
```

#### Configuration for association
When your model implemented the UseCartable interface and you uses your model to add the item to the cart, Laravel Cart will get automatically attributes `title`, `price` in your Eloquent model to use for inserting item to the cart.

But if your Eloquent model doesn't have these attributes (for example, your model field used to store product's name is not a `title` but `name`), you need to reconfigure it so that automatic information retrieval is done correctly. You can do this easily by placing your Eloquent model in two attributes:

```php
<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Jackiedo\Cart\Contracts\UseCartable;
use Jackiedo\Cart\Traits\CanUseCart;

class Product extends Model implements UseCartable {
    use CanUseCart;

    protected $cartTitleField = 'name';        // Your correctly field for product's title
    protected $cartPriceField = 'unit_price';  // Your correctly field for product's price

    ...
}

```

#### Some other method for associated model

```php
/**
 * Determine the UseCartable item has in the cart
 *
 * @param  string|null  $cartInstance  The cart instance name
 * @param  array        $options       Array of additional options, such as 'size' or 'color'
 *
 * @return boolean
 */
public function hasInCart($cartInstance = null, array $options = []);

/**
 * Get all the UseCartable item in the cart
 *
 * @param  string|null  $cartInstance  The cart instance name
 *
 * @return Illuminate\Support\Collection
 */
public function allFromCart($cartInstance = null);

/**
 * Get the UseCartable items in the cart with given additional options
 *
 * @param  string|null  $cartInstance  The cart instance name
 * @param  array        $options       Array of additional options, such as 'size' or 'color'
 *
 * @return Illuminate\Support\Collection
 */
public function searchInCart($cartInstance = null, array $options = []);
```

Example:

```php
$product = Product::find(1);
return ($product->hasInCart('shopping')) ? 'Yes' : 'No';
```

### Custom search building
At the above, we knew how to do a searching for items in the cart. And because informations of each cart item is a Laravel Collection, so that you can easily building a custom searching with placing a Closure into `Cart::search()` method. Laravel Cart will use this Closure with `filter()` method of Collection. Example:

```php
$results = Cart::instance('shopping')->search(function ($cartItem) {
    return $cartItem->id === 1 && $cartItem->associated === "Product";
});
```

You can refer to Collection on the Laravel homepage.

### Dependency injection
In version 2.x of this package, it's possibly to use dependency injection to inject an instance of the Cart class into your controller or other class. Example:

```php
<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Product;
use Illuminate\Http\Request;
use Jackiedo\Cart\Cart;

class TestCartController extends Controller {

    protected $cart;

    protected $request;

    public function __construct(Cart $cart, Request $request)
    {
        $this->cart = $cart;
        $this->request = $request;
    }

    public function content()
    {
        $cartInstance = $this->request->input('instance');

        return $this->cart->instance($cartInstance)->all();
    }
}

```

### Event and listener

| Event Name        | Fired                                        | Parameters          |
| ----------------- | -------------------------------------------- | ------------------- |
| *cart.adding*     | When an item is being added to the cart.     | ($cartItem, $cart); |
| *cart.added*      | When an item was added to the the cart.      | ($cartItem, $cart); |
| *cart.updating*   | When an item in the cart is being updated.   | ($cartItem, $cart); |
| *cart.updated*    | When an item in the cart was updated.        | ($cartItem, $cart); |
| *cart.removing*   | When an item is being removed from the cart. | ($cartItem, $cart); |
| *cart.removed*    | When an item was removed from the cart.      | ($cartItem, $cart); |
| *cart.destroying* | When a cart is being destroyed.              | ($cart);            |
| *cart.destroyed*  | When a cart was destroyed.                   | ($cart);            |

You can easily handle these events, for example:

```php
Event::on('cart.adding', function($attributes, $cart){
    // code
});
```

### Exceptions
The Cart package will throw exceptions if something goes wrong. This way it's easier to debug your code using the Laravel Cart package or to handle the error based on the type of exceptions. The Laravel Cart packages can throw the following exceptions:

| Exception                         | Reason                                                                     |
| --------------------------------- | -------------------------------------------------------------------------- |
| *CartInvalidArgumentException*    | When you misses or enter invalid argument (such as title, qty...).         |
| *CartInvalidItemIdException*      | When the `$cartItemId` that got passed doesn't exists in the current cart. |
| *CartUnknownModelException*       | When an model is associated to a cart item row is not exists.              |

## License
[MIT](LICENSE) © Jackie Do

## Thanks for use
Hopefully, this package is useful to you.