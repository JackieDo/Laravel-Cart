# Laravel Cart

[![Run tests](https://github.com/JackieDo/Laravel-Cart/actions/workflows/run-tests.yml/badge.svg?branch=v3.0)](https://github.com/JackieDo/Laravel-Cart/actions/workflows/run-tests.yml)
[![Build Status](https://api.travis-ci.org/JackieDo/Laravel-Cart.svg?branch=v3.0)](https://travis-ci.org/JackieDo/Laravel-Cart)
[![Total Downloads](https://poser.pugx.org/jackiedo/cart/downloads)](https://packagist.org/packages/jackiedo/cart)
[![Latest Stable Version](https://poser.pugx.org/jackiedo/cart/v/stable)](https://packagist.org/packages/jackiedo/cart)
[![License](https://poser.pugx.org/jackiedo/cart/license)](https://packagist.org/packages/jackiedo/cart)

Laravel Cart is a package used to create and manage carts (such as shopping, recently viewed, compared items...) in Laravel application.

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
Currently, the Laravel Cart has three branches that are compatible with the following versions of Laravel:

| Branch                                                     | Tag releases | Laravel version  |
| ---------------------------------------------------------- | ------------ | ---------------- |
| [v1.0](https://github.com/JackieDo/Laravel-Cart/tree/v1.0) | 1.*          | 4.x only         |
| [v2.0](https://github.com/JackieDo/Laravel-Cart/tree/v2.0) | 2.*          | 5.x only         |
| [v3.0](https://github.com/JackieDo/Laravel-Cart/tree/v3.0) | 3.*          | 5.x or above     |

Currently, versions `v1.0` and `v2.0` are no longer supported. Version `v3.0` was created with more advanced features, and has a completely different way of working from the old version.

## Important note (*)
Version 3.0 has a different structure and working method from previous versions. Therefore, if you have used previous versions and do not want to change or want to learn new ways of working, I recommend that you do not install this version. Staying with the old version, it doesn't give you any new features, but gives you safety.

On the contrary, if you choose version 3.0 to work, you will own particularly useful features that previous versions did not have. It is important that you read the documentation carefully to work properly.

## Documentation
You can find documentation for version `v3.0` [here](https://jackiedo.github.io/Laravel-Cart). Documentations for older versions, please see the respective branches.

## Testing
The package has been tested through over 120 test cases with GitHub Actions from PHP version 7.1 (Laravel 5.8) to 8.2 (Laravel 10.x). Detailed information about test cases please see [here](https://github.com/JackieDo/Laravel-Cart/actions/workflows/run-tests.yml).

## License
[MIT](LICENSE) © Jackie Do
