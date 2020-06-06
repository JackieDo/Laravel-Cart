# Installation
You can install Laravel Cart through [Composer](https://getcomposer.org) with the steps below.

## Require package
At the root of your application directory, run the following command:

```shell
$ composer require jackiedo/cart:3.*
```

**Note:** Since Laravel 5.5, [service providers and aliases are automatically registered](https://laravel.com/docs/5.5/packages#package-discovery), you don't need to do anything more. But if you are using Laravel 5.4 or earlier, you must perform two more steps below.

## Register service provider
Open `config/app.php`, and add a new line to the `providers` section:

```php
'Jackiedo\Cart\CartServiceProvider',
```

**Note:** From Laravel 5.1, you should write as `Jackiedo\Cart\CartServiceProvider::class,`

## Register facade
Add the following line to the `aliases` section in file `config/app.php`:

```php
'Cart' => 'Jackiedo\Cart\Facades\Cart',
```

**Note:** From Laravel 5.1, you should write as `'Cart' => Jackiedo\Cart\Facades\Cart::class,`
