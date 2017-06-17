# Laravel Cart
[![Total Downloads](https://poser.pugx.org/jackiedo/cart/downloads)](https://packagist.org/packages/jackiedo/cart)
[![Latest Stable Version](https://poser.pugx.org/jackiedo/cart/v/stable)](https://packagist.org/packages/jackiedo/cart)
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
    - [Event and listener](#event-and-listener)
    - [Exceptions](#exceptions)
* [License](#license)
* [Thanks from author](#thanks-for-use)

## Versions and compatibility
Currently, Laravel Cart have two branchs compatible with Laravel 4.x and 5.x as follow:

| Branch                                                     | Laravel version  |
| ---------------------------------------------------------- | ---------------- |
| [v1.0](https://github.com/JackieDo/Laravel-Cart/tree/v1.0) | 4.x              |
| [v2.0](https://github.com/JackieDo/Laravel-Cart/tree/v2.0) | 5.x              |

> **Note:** This documentation is use for Laravel 4.x. If you use Laravel 5+, you should read at [here](https://github.com/JackieDo/Laravel-Cart/tree/v2.0)

## Installation
You can install this package through [Composer](https://getcomposer.org).

- First, edit your project's `composer.json` file to require `jackiedo/cart`. Add following line to the `require` section:
```
"jackiedo/cart": "1.*"
```

- Next step, we run Composer update commend from the Terminal on your project source directory:
```shell
$ composer update
```

> **Note:** Instead of performing the above two steps, you can perform faster with the command line `$ composer require jackiedo/cart:1.*` from Terminal

- Once update operation completes, the third step is add the service provider. Open `app/config/app.php`, and add a new item to the `providers` section:
```
'Jackiedo\Cart\CartServiceProvider',
```

- And the final step is add the following line to the `aliases` section in file `app/config/app.php`:
```
'Cart' => 'Jackiedo\Cart\Facades\Cart',
```

## Basic usage

### The Cart facade
Laravel Cart has a facade with name is `Jackiedo\Cart\Facades\Cart`. You can do any cart operation through this facade.

### Named your cart instances
Laravel Cart supports multiple instances of the cart. This is useful for using different carts for different management purposes, such as shopping cart, whishlist items, recently views...

Whenever you create a shopping cart, you should name it so that it can be distinguished from other carts. You can set name for an instance of the cart by calling `Cart::instance($instanceName)`. From this point of time, the active instance of the cart will have a specified name, so whenever you perform a cart operation, you must specify a specific cart.

Example:
```php
Cart::instance('shopping');
```

If you want to switch between instances, you just call `Cart::instance($otherName)` again, and you're working with the other instance.

So a little example:
```php
// Create new instance of the cart with named is `shopping`
$cart = Cart::instance('shopping');

// Perform a something with this instance
$cart->doSomething();

// Switch to `whishlist` cart and do something
Cart::instance('wishlist')->doSomething();

// Do another thing also with `whishlist` cart
Cart::doAnother();

// Switch to `recent-view` cart and do something
Cart::instance('recent-view')->doSomething();

// Go back to work with `shopping` cart
Cart::instance('shopping')->doSomething();
...
```

**Note:**
- The default cart instance is called `default`, so when you're not using instances, example `Cart::doSomething();` is the same as `Cart::instance('default')->doSomething();`
- Keep in mind that the cart stays in the last set instance for as long as you don't set a different one during script execution.

### Get current cart instance
You can easily get current cart instance name by calling `Cart::getInstance()` method.

### Add an item to cart
Adding an item to the cart is really simple, you just use the `Cart::add()` method, which accepts a variety of parameters.

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
Cart::add($rawId, $title[, $qty = 1[, $price = 0[, $options = array()]]]);
```

Example:
```php
// Add an article to the `recent-view` cart
$recentView = Cart::instance('recent-view')->add('AID0782000', 'An example article');

// Add an product to the `shopping` cart
$shoppingCartItem = Cart::instance('shopping')->add(37, 'Polo T-shirt for men', 5, 17.5, ['color' => 'red', 'size' => 'M']);
```

The result of this method is an instance of `Jackiedo\Cart\CartItem` class (extended from `Illuminate\Support\Collection`) and has structured as follows:
```
{
    id         : "3fbdcdad4cbcee36f36ee15d89505d54",
    raw_id     : 37,
    title      : "Polo T-shirt for men",
    qty        : 5,
    price      : 17.5,
    subtotal   : 87.5,
    options    : {
        color : "red",
        size  : "M"
    },
    associated : null
}
```

So, you can access it's property by method `get()` of Laravel Collection instance.

Example:
```php
// Get id of the cart item. This ID is used to distinguish items with different attributes in the cart.
$thisCartItemId = $shoppingCartItem->get('id');  // 3fbdcdad4cbcee36f36ee15d89505d54
```

But with some enhancements to this CartItem class, you can more easily access entity attributes in a more succinct way:

Example:
```php
// Get id of this cart item
$thisCartItemId = $shoppingCartItem->id;            // 3fbdcdad4cbcee36f36ee15d89505d54

// Get sub total price of this cart item
$thisSubTotal = $shoppingCartItem->subtotal;        // 87.5

// Get color option of this cart item
$thisSubTotal = $shoppingCartItem->options->color;  // red
...
```

> **Note:** Cart item's ID is used to distinguish items with different attributes in the cart. So with the same product, but when you add to the cart with different options, you will have different cart items with different IDs.

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
Cart::update($cartItemId, $quantity);
Cart::update($cartItemId, $attributes);
```

Example:
```php
$cartItemId = "3fbdcdad4cbcee36f36ee15d89505d54";

// Update title and options
$cartItem = Cart::instance('shopping')->update($cartItemId, ['title' => 'New item title', 'options' => ['color' => 'yellow']]);

// or only update quantity
$cartItem = Cart::instance('shopping')->update($cartItemId, 10);
```

> **Note:** You can only update attributes about title, quantity, price and options. Whenever you update cart item's info, the cart item's ID can be changed.

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
$item = Cart::instance('shopping')->get('3fbdcdad4cbcee36f36ee15d89505d54');
```

```
{
    id         : "3fbdcdad4cbcee36f36ee15d89505d54",
    raw_id     : 37,
    title      : "New item title",
    qty        : 5,
    price      : 17.5,
    subtotal   : 87.5,
    options    : {
        color : "yellow",
        size  : "M"
    },
    associated : null
}
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
```

And the results may have the following structure:

```
{
    562e261883ba74c800c739494f62667b: {
        id         : "562e261883ba74c800c739494f62667b",
        raw_id     : 37,
        title      : "Polo T-shirt for men",
        qty        : 1,
        price      : 17.5,
        subtotal   : 17.5,
        options    : {
            color : "yellow",
            size  : "L"
        },
        associated : null
    },
    3fbdcdad4cbcee36f36ee15d89505d54: {
        id         : "3fbdcdad4cbcee36f36ee15d89505d54",
        raw_id     : 37,
        title      : "Polo T-shirt for men",
        qty        : 5,
        price      : 17.5,
        subtotal   : 87.5,
        options    : {
            color : "red",
            size  : "M"
        },
        associated : null
    }
}
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
Return the number of quantities for all items in the cart.

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

> **Note:** The `$allScope` parameter use to determine that the filter is satisfied for all attributes simultaneously or in combination.

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
You need to know that all result of cart content (response by methods such as `Cart::all()`, `Cart::search()`), every cart item (response by methods such as `Cart::add()`, `Cart::get()`, `Cart::update()`) and cart item's options (is property of one cart item) are return a Laravel Collection, so all methods you can call on a Laravel Collection are also available on them.

Example:
```php
$count = Cart::instance('shopping')->all()->count();
```

And another example (group by attribute):
```php
$groupByTitle = Cart::instance('shopping')->all()->groupBy('title');
```

And may be you will see:
```
{
    Polo neck T-shirt for men: [
        {
            id         : "e4656c2e79429b833b5569062f599cab",
            raw_id     : 1,
            title      : "Polo neck T-shirt for men",
            qty        : 5,
            price      : 17.5,
            subtotal   : 87.5,
            options    : {
                color : "yellow",
                size  : "M"
            },
            associated : "Product"
        },
        {
            id         : "3fbdcdad4cbcee36f36ee15d89505d54",
            raw_id     : 1,
            title      : "Polo neck T-shirt for men",
            qty        : 10,
            price      : 17.5,
            subtotal   : 175,
            options    : {
                color : "red",
                size  : "M"
            },
            associated : "Product"
        }
    ]
}
```

### Eloquent model association
A special feature of Laravel Cart is association an Eloquent model with the cart item in the cart. Let's say you have a `Product` model in your application. With this feature, you can tell the cart that an item in the cart, is associated to a instance `Product` model. That way, you can access your instance of model right from instance of `CartItem`!

To be ready for this, Laravel Cart has one interface (with namespace is `Jackiedo\Cart\Contracts\UseCartable`) and one trait (with namespace is `Jackiedo\Cart\Traits\CanUseCart`). The rest is you just apply for your Eloquent model.

#### Preparing for association:
It's easily to do this. Your model just only implements the `UseCartable` interface and use the `CanUseCart` trait:
```php
<?php

use Jackiedo\Cart\Contracts\UseCartable; // Interface
use Jackiedo\Cart\Traits\CanUseCart;     // Trait

class Product extends Eloquent implements UseCartable {
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
```

And the result will be:
```
{
    id         : "3fbdcdad4cbcee36f36ee15d89505d54",
    raw_id     : 1,
    title      : "Polo neck T-shirt for men",
    qty        : 5,
    price      : 17.5,
    subtotal   : 87.5,
    options    : {
        color : "red",
        size  : "M"
    },
    associated : "Product"
}
```

Please, notice the associated attribute of the cart item. Do you see the value is `Product`? Wow! now you can be able to directly access your model from this cart item instance through `model` property. Example:

```php
return $cartItem->model->name; // Get name of your product
```

#### Configuration for association
When your model implemented the UseCartable interface and you uses your model to add the item to the cart, Laravel Cart will get automatically attributes `title`, `price` in your Eloquent model to use for inserting item to the cart.

But if your Eloquent model doesn't have these attributes (for example, your model field used to store product's name is not a `title` but `name`), you need to reconfigure it so that automatic information retrieval is done correctly. You can do this easily by placing your Eloquent model in two attributes:

```php
<?php;

use Jackiedo\Cart\Contracts\UseCartable;
use Jackiedo\Cart\Traits\CanUseCart;

class Product extends Eloquent implements UseCartable {
    use CanUseCart;

    protected $cartTitleField = 'name';        // Your correctly field for product's title
    protected $cartPriceField = 'unit_price';  // Your correctly field for product's price

    ...
}

```

#### Some other methods for associated model

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

### Event and listener
The Laravel Cart package has events build in. Currently, there are eight events available for you to listen for.

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

You can easily handle these events. For example, we can listen events through the Event facade:
```php
<?php

use Illuminate\Support\Facades\Event;
...
    Event::on('cart.adding', function($cartItem, $cart){
        // code
    });
...

```

### Exceptions
The Laravel Cart package will throw exceptions if something goes wrong. This way it's easier to debug your code using the Laravel Cart package or to handle the error based on the type of exceptions. The Laravel Cart packages can throw the following exceptions:

| Exception                         | Reason                                                                     |
| --------------------------------- | -------------------------------------------------------------------------- |
| *CartInvalidArgumentException*    | When you missed or entered invalid argument (such as title, qty...).       |
| *CartInvalidItemIdException*      | When the `$cartItemId` that got passed doesn't exists in the current cart. |
| *CartUnknownModelException*       | When an associated model of the cart item row doesn't exists.              |

## License
[MIT](LICENSE) © Jackie Do

## Thanks for use
Hopefully, this package is useful to you.