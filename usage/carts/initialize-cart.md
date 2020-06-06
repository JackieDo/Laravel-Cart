# Initialize the cart
In Laravel, you can initialize the cart in two ways:

- [Using the Cart facade](#using-the-cart-facade).
- [Using dependency injection](#using-dependency-injection).

## Using the Cart facade
Laravel Cart has a facade with name is `Jackiedo\Cart\Facades\Cart`. Just use this facde, you can do any cart operation.

**Example:**

```php
<?php namespace YourNamespace;

use Jackiedo\Cart\Facades\Cart;

class YourClass
{
    // ...
    public function yourMethod()
    {
        // ...
        $variable = Cart::doSomething();
    }
    // ...
}
```

## Using dependency injection
Sine version 2.x of this package, it's possibly to use dependency injection to inject an instance of the Cart class into your controller or other class. In version 3.x, this feature is the same.

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
        $this->cart = $cart;
    }

    public function content()
    {
        $variable = $this->cart->doSomething();
    }

    public function addItem()
    {
        $variable = $this->cart->doSomething();
    }
}

```
