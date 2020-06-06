# Configuration
Before using, you should perform configuration for the package. This can be done easily in two steps:

- Publish configuration file.
- Configure settings.

## Publish configuration file
At the root of your application directory, run the following command:

```shell
$ php artisan vendor:publish --provider="Jackiedo\Cart\CartServiceProvider" --tag="config"
```

This will create a `config/cart.php` file in root folder of your application. Through it, you can set the settings. Also, make sure you check for changes to the original config file in this package between releases.

## Configure settings
Currently there are the following settings:

#### Default cart name
The `default_cart_name` setting allows you to set the default name of the cart in case you do not specify a specific name.

#### None commercial carts
By default, the carts created will be used for commercial purposes, ie will contain money-related information.

The `none_commercial_carts` setting allows you to specify which carts (by the name) are not for commercial use but used for other purposes such as storing recent viewed items, compared items... They are have no information regarding money.

#### Use built-in tax system
The `use_builtin_tax` setting allows you to set the default state of using the built-in taxing system or not every time you initialize the cart.

#### Default tax rate
The `default_tax_rate` setting is the default tax rate used for each tax of the cart if you do not set the rate attribute every time apply a tax into cart.

#### Default rules of actions
The `default_action_rules` is the default rules attribute of action when you apply an action into cart or item. The value of this setting shows how to calculate the amount for the action.

The available keys and values ​​of this setting are:

| Key                    | Accepted values                                                                       |
| ---------------------- | ------------------------------------------------------------------------------------- |
| enable                 | true \| false                                                                         |
| taxable                | true \| false                                                                         |
| allow\_others\_disable | true \| false                                                                         |
| disable\_others        | null \| 'previous\_actions' \| 'same\_group\_previous\_actions' \| 'previous\_groups' |
| include\_calculations  | null \| 'previous\_actions' \| 'same\_group\_previous\_actions' \| 'previous\_groups' |
| max_amount             | null \| a float number                                                                |
| min_amount             | null \| a float number                                                                |

These values ​​will be explained in more detail in advanced usage.

#### Action groups order
The `action_groups_order` setting allows to prioritize groups of actions in ascending order. If not set, actions will be sorted according to the time of applied. In contrast, the actions will be sorted by groups order first, then sorted by the time of applied.
