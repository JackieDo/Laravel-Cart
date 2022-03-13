## Laravel Cart
A package used to create and manage carts in Laravel application.

[![Run tests](https://github.com/JackieDo/Laravel-Cart/actions/workflows/run-tests.yml/badge.svg?branch=v3.0)](https://github.com/JackieDo/Laravel-Cart/actions/workflows/run-tests.yml)
[![Build Status](https://api.travis-ci.org/JackieDo/Laravel-Cart.svg?branch=v3.0)](https://travis-ci.org/JackieDo/Laravel-Cart)
[![Total Downloads](https://poser.pugx.org/jackiedo/cart/downloads)](https://packagist.org/packages/jackiedo/cart)
[![Latest Stable Version](https://poser.pugx.org/jackiedo/cart/v/stable)](https://packagist.org/packages/jackiedo/cart)
[![License](https://poser.pugx.org/jackiedo/cart/license)](https://packagist.org/packages/jackiedo/cart)

## Features
- Session based system.
- Support multiple cart instances.
- Classification of commercial and non-commercial carts.
- Grouping the carts.
- Quickly insert items with your own item models.
- Taxation on the cart level (with built-in taxing system).
- Applying actions on the cart and item level (such as discount, service charge, shipping cost...).
- Exporting details as Laravel Collection.
- Allows storage of extended information.
- Control of firing events.

## Versions and compatibility
Laravel Cart has three branches that are compatible with the following versions of Laravel:

| Branch                                                     | Tag releases | Laravel version  |
| ---------------------------------------------------------- | ------------ | ---------------- |
| [v1.0](https://github.com/JackieDo/Laravel-Cart/tree/v1.0) | 1.*          | 4.x only         |
| [v2.0](https://github.com/JackieDo/Laravel-Cart/tree/v2.0) | 2.*          | 5.x only         |
| [v3.0](https://github.com/JackieDo/Laravel-Cart/tree/v3.0) | 3.*          | 5.x or above     |

Currently, versions `v1.0` and `v2.0` are no longer supported. Version `v3.0` was created with more advanced features, and has a completely different way of working from the old version.

## Documentation
This [documentation site](https://jackiedo.github.io/Laravel-Cart) is written specifically for v3.0. If you need documentation for older versions, please see the respective branches.

## Testing
The package has been tested through over 120 test cases with GitHub Actions from PHP version 7.1 (Laravel 5.8) to 8.1 (Laravel 9.x). Detailed information about test cases please see [here](https://github.com/JackieDo/Laravel-Cart/actions/workflows/run-tests.yml).

## License
[MIT](https://github.com/JackieDo/Laravel-Cart/blob/master/LICENSE) © Jackie Do
