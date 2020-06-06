# Eloquent model association
A special feature of Laravel Cart is association an Eloquent model with the cart item in the cart. Let's say you have a `Product` model in your application. With this feature, you can tell the cart that an item in the cart, is associated to a instance `Product` model. That way, you can access your instance of model right from instance of the cart item!

To be ready for this, Laravel Cart has one interface (with namespace is `Jackiedo\Cart\Contracts\UseCartable`) and one trait (with namespace is `Jackiedo\Cart\Traits\CanUseCart`). The rest is you just apply for your Eloquent model.

## Preparing for association:
It's easily to do this. Your Eloquent model just only need implements the `UseCartable` interface and use the `CanUseCart` trait.

```php
<?php namespace YourNamespace;

use Illuminate\Database\Eloquent\Model;

use Jackiedo\Cart\Contracts\UseCartable; // Interface
use Jackiedo\Cart\Traits\CanUseCart;     // Trait

class YourEloquent extends Model implements UseCartable {
    use CanUseCart;
    // ...
}

```

## Make an association
If your model implemented the UseCartable interface and you uses your model to add the item to the cart, it will associate automatically.

```php
/**
 * Add an item into the items container
 *
 * @param  array   $attributes The item attributes
 * @param  boolean $withEvent  Enable firing the event
 *
 * @return Jackiedo\Cart\Item|null
 */
Cart::addItem([
    'model' => $yourEloquent,
    // ...
]);
```

Or, with another way:

```php
/**
 * Add the UseCartable item to the cart
 *
 * @param  Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
 * @param  array                     $attributes The additional attributes
 * @param  boolean                   $withEvent  Enable firing the event
 *
 * @return Jackiedo\Cart\Item|null
 */
YourEloquent::addToCart($cartOrName, array $attributes = [], $withEvent = true)
```

Example:

```php
// Get your model
$model = YourEloquent::find(1);

// Add 5 items of this model to the cart
$cartItem = Cart::name('shopping')->addItem([
    'model'    => $model,
    'quantity' => 5
]);

// Or you can do this by another way
$cartItem = $model->addToCart('shopping', [
    'quantity' => 5
]);
```

Do you remember that the added item instance has an attribute called `associated_class`? Now when you retrieve this attribute (`$cartItem->getAssociatedClass()`), you will see the name of the class that this item associated with. Furthermore, now you can be able to directly access your model from this item instance through the `getModel()` method. Example:

```php
$model = $cartItem->getModel(); // Retrieve your eloquent model from the added item
```

You can also do this through the `Details` instance of this item:

```php
$model = $cartItem->getDetails()->model;
```

## Configuration for association
When your model implemented the UseCartable interface and you uses your model to add the item to the cart, Laravel Cart will get automatically the `title`, `price` and `id` attributes in your Eloquent model to use for inserting item to the cart.

But if your Eloquent model doesn't have these attributes (for example, your model field used to store product's name is not a `title` but `name`), you need to reconfigure it so that automatic information retrieval is done correctly. You can do this easily by placing your Eloquent model in two attributes:

```php
<?php namespace YourNamespace;

use Illuminate\Database\Eloquent\Model;
use Jackiedo\Cart\Contracts\UseCartable;
use Jackiedo\Cart\Traits\CanUseCart;

class YourEloquent extends Model implements UseCartable {
    use CanUseCart;

    protected $cartTitleField = 'name';        // Your correctly field for product's title
    protected $cartPriceField = 'unit_price';  // Your correctly field for product's price
    // ...
}

```

## Some other methods for associated model

```php
/**
 * Determines the UseCartable item has in the cart
 *
 * @param  Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
 * @param  array                     $filter     Array of additional filter
 *
 * @return boolean
 */
public function hasInCart($cartOrName, array $filter = []);

/**
 * Get all the UseCartable item in the cart
 *
 * @param  Jackiedo\Cart\Cart|string $cartOrName The cart instance or the name of the cart
 *
 * @return array
 */
public function allFromCart($cartOrName);
```

Example:

```php
$product = YourEloquent::find(1);

return ($product->hasInCart('shopping')) ? 'Yes' : 'No';
```
