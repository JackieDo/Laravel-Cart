# Built-in taxing system
Laravel Cart has a built-in tax calculation system. This system allows to apply one or many taxes to the entire cart, not individual items in the cart. This means that it will apply to all taxable items and taxable actions. If you don't like the way the tax system works, you have the right to turn it off.

## How the taxing system works?
The built-in taxing system of Laravel Cart calculates the tax amount through 4 steps:

- Step 1: Calculates the taxable amount of all items in the cart (including taxable actions that apply to the item).
- Step 2: Calculates the taxable amount of applied actions to the cart.
- Step 3: Calculates the total taxable amount by sum the amounts in steps 1 and 2.
- Step 4: Calculates the tax amount based on the tax rate and the total taxable amount in step 3.

## Activate built-in taxing system
If you decide to use this system, you first need to activate it. You can do that in two ways:

#### Using the configuration file
You need to set the value of the `use_builtin_tax` setting to true (see [here](configuration#use-built-in-tax-system)). This will enable this system to be enabled by default for all carts when initialized.

#### Using the cart method
If you want to set the activation of this system for different carts, use the `useBuiltinTax()` method with the following syntax:

```php
/**
 * Enable or disable built-in tax system for the cart.
 * This is only possible if the cart is empty
 *
 * @param  boolean $status
 *
 * @return $this
 */
public function useBuiltinTax($status = true);
```

**Note:**
- You can only set this status if the cart is empty, meaning that no items have been added, no taxes or actions have been applied.
- If the cart is a non-commcercial cart, this taxing system will be disabled.

## Check the activation status
To check if current cart is using this taxing system, simply use the following method:

```php
/**
 * Determines if current cart is enabled built-in tax system
 *
 * @return boolean
 */
public function isEnabledBuiltinTax();
```
