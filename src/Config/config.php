<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default cart name
    |--------------------------------------------------------------------------
    |
    | This setting allows you to set the default name of the cart in case you
    | do not specify a specific name.
    |
    */
    'default_cart_name' => 'default',

    /*
    |--------------------------------------------------------------------------
    | None commercial carts
    |--------------------------------------------------------------------------
    |
    | This setting allows you to specify which carts (by the name) are not for
    | commercial use but used for other purposes such as storing recent viewed
    | items, compared items... They are have no information regarding money.
    |
    */
    'none_commercial_carts' => [
        // 'example_cart_name'
    ],

    /*
    |--------------------------------------------------------------------------
    | Use built-in tax system
    |--------------------------------------------------------------------------
    |
    | This setting allows you to set the default state of using the built-in
    | taxing system or not every time you initialize the cart.
    |
    */
    'use_builtin_tax' => true,

    /*
    |--------------------------------------------------------------------------
    | Default tax rate
    |--------------------------------------------------------------------------
    |
    | This is the default tax rate value used for each tax of the cart if you do
    | not set the rate attribute every time apply a tax into cart.
    |
    */
    'default_tax_rate' => 10,

    /*
    |--------------------------------------------------------------------------
    | Default rules of actions
    |--------------------------------------------------------------------------
    |
    | This is the default rules attribute of action when you apply an action
    | into cart or item. The value of this setting shows how to calculate the
    | amount for the action. For details about the available keys and values ​​of
    | this setting, please see https://github.com/JackieDo/Laravel-Cart
    |
    */
    'default_action_rules' => [
        'enable'               => true,
        'taxable'              => true,
        'allow_others_disable' => true,
        'disable_others'       => null,
        'include_calculations' => 'same_group_previous_actions',
        'max_amount'           => null,
        'min_amount'           => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Action groups order
    |--------------------------------------------------------------------------
    |
    | This setting allows to prioritize groups of actions in descending order.
    | If not set, actions will be sorted according to the time of applied. In
    | contrast, the actions will be sorted by groups order first, then sorted
    | by the time of applied.
    |
    */
    'action_groups_order' => [
        // 'example_action_group_name'
    ],
];
