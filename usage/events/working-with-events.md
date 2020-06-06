# Working with events
The Laravel Cart package has events build in. Currently, there are 26 events available for you to listen for.

| Event Name             | Fired                                                    | Parameters              |
| ---------------------- | -------------------------------------------------------- | ----------------------- |
| *cart.item.adding*     | When an item is being added to the cart.                 | ($item)                 |
| *cart.item.added*      | When an item was added to the the cart.                  | ($item)                 |
| *cart.item.updating*   | When an item in the cart is being updated.               | (&$attributes, $item)   |
| *cart.item.updated*    | When an item in the cart was updated.                    | ($item)                 |
| *cart.item.removing*   | When an item is being removed from the cart.             | ($item)                 |
| *cart.item.removed*    | When an item was removed from the cart.                  | ($itemHash, $cart)      |
| *cart.item.clearing*   | When all items is being removed from the cart.           | ($cart)                 |
| *cart.item.cleared*    | When all items was removed from the cart.                | ($cart)                 |
| *cart.tax.applying*    | When a tax is being applied to the cart.                 | ($tax)                  |
| *cart.tax.applied*     | When a tax was applied to the the cart.                  | ($tax)                  |
| *cart.tax.updating*    | When a tax in the cart is being updated.                 | (&$attributes, $tax)    |
| *cart.tax.updated*     | When a tax in the cart was updated.                      | ($tax)                  |
| *cart.tax.removing*    | When a tax is being removed from the cart.               | ($tax)                  |
| *cart.tax.removed*     | When a tax was removed from the cart.                    | ($taxHash, $cart)       |
| *cart.tax.clearing*    | When all taxes is being removed from the cart.           | ($cart)                 |
| *cart.tax.cleared*     | When all taxes was removed from the cart.                | ($cart)                 |
| *cart.action.applying* | When an action is being applied to the cart or item.     | ($action)               |
| *cart.action.applied*  | When an action was applied to the the cart or item.      | ($action)               |
| *cart.action.updating* | When an action in the cart or item is being updated.     | (&$attributes, $action) |
| *cart.action.updated*  | When an action in the cart or item was updated.          | ($action)               |
| *cart.action.removing* | When an action is being removed from the cart or item.   | ($action)               |
| *cart.action.removed*  | When an action was removed from the cart or item.        | ($actionHash, $cart)    |
| *cart.action.clearing* | When all actions is being removed from the cart or item. | ($cart)                 |
| *cart.action.cleared*  | When all actions was removed from the cart or item.      | ($cart)                 |
| *cart.destroying*      | When a cart is being destroyed.                          | ($cart)                 |
| *cart.destroyed*       | When a cart was destroyed.                               | ()                      |

For example, we can listen events in the boot method of your `EventServiceProvider`.

```php
<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    // ...

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen('cart.item.added', function ($item) {
            if ($item->getQuantity() >= 2) {
                $item->applyAction([
                    // ...
                ]);
            }
        });

        // ...
    }

    // ...
}
```

**Note:**
- These events can be disabled by passing the `$withEvent` parameter with a value of `false` to the corresponding methods. Example: `$cart->addItem([...], false);`
- The parameters of events are actual entities. Be careful when working with them. It is possible to change the values ​​in the cart.
- See more about Laravel Events [here](https://laravel.com/docs/7.x/events).
