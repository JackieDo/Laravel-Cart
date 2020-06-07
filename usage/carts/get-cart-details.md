# Get details of cart as Laravel Collection
**Method syntax:**

```php
/**
 * Get all information of cart as a collection
 *
 * @param  boolean $withItems   Include details of added items in the result
 * @param  boolean $withActions Include details of applied actions in the result
 * @param  boolean $withTaxes   Include details of applied taxes in the result
 *
 * @return Jackiedo\Cart\Details
 */
public function getDetails($withItems = true, $withActions = true, $withTaxes = true);
```

The above method allows you to have a complete overview of the information of the cart. This method returns a Laravel Collection, it is really useful for sending asynchronous data (such as working with ajax...), because you can easily convert it into a JSON format.

In addition, when you return the result of this method in the Laravel Controller, it is also automatically converted into JSON. Therefore, if your browser has a built-in extension for JSON parsing, you can quickly capture the details of the cart.

**Example:**

```php
<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Product;
use Illuminate\Http\Request;
use Jackiedo\Cart\Cart;

class TestCartController extends Controller {

    protected $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart->name('shopping');
    }

    public function addItemUsingAjax($productId)
    {
        // ...
        $cartItem = $this->cart->addItem([
            // ...
        ]);
        // ...

        return $this->cart->getDetails()->toJson();
    }
}

```

## Working with Details Collection
You need to know that result of the `getDetails()` method is an instance of the `Jackiedo\Cart\Details` class and entended from Laravel Collection, so all methods you can call on a Laravel Collection are also available on it.

**Example:**

```php
$groupByTitle = Cart::name('shopping')->getDetails()->get('items')->groupBy('title');

return $groupByTitle;
```

As you know, each key in Laravel Collection can accessed by the `get()` method, but with some enhancements to the `Details` class, you can more easily access entity attributes in a more succinct way.

**Example:**

```php
// Original way
$allItems   = $cartDetails->get('items');
$neededItem = $allItems->get('item_12324nsadsr96hjasdf7858');
$options    = $neededItem->get('options');
$size       = $options->get('size');

// Succinct way
$cartDetails->items->item_12324nsadsr96hjasdf7858->options->size;
```

Or with another example:

```php
// Original way
$groupByTitle = Cart::name('shopping')->getDetails()->get('items')->groupBy('title');

// Succinct way
$groupByTitle = Cart::name('shopping')->getDetails()->items->groupBy('title');
```

**Note:**
- The values in this Details Collection are cloned from the real instance. Therefore, any direct changes to them do not change the information contained in the cart. To change the values ​​in the cart, you must use the methods provided.
- This Details Collection always has the following keys:
    + `type`:
        * Description: The type of this collection
        * Type: string
    + `name`:
        * Description: The name of the cart
        * Type: string
    + `commercial_cart`:
        * Description: Indicates whether this cart is a commercial cart
        * Type: boolean
    + `enabled_builtin_tax`:
        * Description: Indicates whether this cart use built-in tax system
        * Type: boolean
    + `items_count`:
        * Description: The number of added items in the cart
        * Type: int
    + `quantities_sum`:
        * Desciption: The sum quantities of all added items
        * Type: int
    + `items`:
        * Description: All added items
        * Type: array
    + `extra_info`:
        * Description: The extended information of the cart
        * Type: array
- If the cart is a commercial cart, this Details Collection will have the following keys as well:
    + `items_subtotal`:
        * Description: The sum subtotals of all added items in the cart
        * Type: float
    + `actions_count`:
        * Description: The number of applied actions to the cart
        * Type: int
    + `action_amount`:
        * Description: The calculated amount of applied actions
        * Type: float
    + `total`:
        * Description: The final total amount of the cart, calculated as the sum of all amounts
        * Type: float
    + `applied_actions`:
        * Description: All applied actions to the cart
        * Type: array
- If the built-in tax system of the cart is enabled, this Details Collection will have more another keys:
    + `subtotal`:
        * Description: The subtotal amount of the cart, calculated by the sum of `items_subtotal` and `actions_amount` keys
        * Type: float
    + `taxes_count`:
        * Description: The number of applied taxes to the cart
        * Type: int
    + `taxable_amount`:
        * Description: The amount will be taxable of the cart
        * Type: float
    + `tax_rate`:
        * Description: The sum tax rates of all applied taxes
        * Type: float
    + `tax_amount`:
        * Description: The tax amount of the cart, calculated based on `tax_rate` and `taxable_amount` keys
        * Type: float
    + `applied_taxes`:
        * Description: All apllied taxes
        * Type: array
