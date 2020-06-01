<?php

use Illuminate\Support\Collection;
use Jackiedo\Cart\Action;
use Jackiedo\Cart\Cart;
use Jackiedo\Cart\Contracts\UseCartable;
use Jackiedo\Cart\Exceptions\InvalidArgumentException;
use Jackiedo\Cart\Exceptions\InvalidCartNameException;
use Jackiedo\Cart\Item;
use Jackiedo\Cart\Tax;
use Orchestra\Testbench\TestCase;

require_once __DIR__ . '/Traits/CommonSetUp.php';
require_once __DIR__ . '/Mocks/UseCartableProduct.php';

class CartTest extends TestCase
{
    use CommonSetUp;

    /**
     * Always have a default cart name.
     *
     * @testdox Always have a default cart name.
     * @test
     */
    public function always_have_a_default_cart_name()
    {
        $cart        = $this->initCart();
        $defaultName = config('cart.default_cart_name');

        $this->assertEquals($defaultName, $cart->getName());
    }

    /**
     * Can name the cart differently from the default cart name using the name() method.
     *
     * @testdox Can name the cart differently from the default cart name using the name() method.
     * @test
     */
    public function can_name_the_cart_differently_from_the_default_cart_name_using_the_name_method()
    {
        $cart        = $this->initCart()->name('demo_another_name');
        $defaultName = config('cart.default_cart_name');

        $this->assertNotEquals($defaultName, $cart->getName());
    }

    /**
     * Cannot use the extra_info keyword to name the cart.
     *
     * @testdox Cannot use the extra_info keyword to name the cart.
     * @test
     */
    public function cannot_use_the_extra_info_keyword_to_name_the_cart()
    {
        $this->assertException(InvalidCartNameException::class);

        $cart = $this->initCart()->name('extra_info');
    }

    /**
     * Can group multiple carts into a group by using the dot to separate the name of the cart into group names.
     *
     * @testdox Can group multiple carts into a group by using the dot to separate the name of the cart into group names.
     * @test
     */
    public function can_group_multiple_carts_into_a_group_by_using_the_dot_to_separate_the_name_of_the_cart_into_group_names()
    {
        $cart1 = $this->initCart()->name('grouped.cart1');
        $cart2 = $this->initCart()->name('grouped.cart2');

        $this->assertTrue($cart1->hasBeenGrouped());
        $this->assertTrue($cart2->hasBeenGrouped());
        $this->assertEquals($cart1->getGroupName(), $cart2->getGroupName());
    }

    /**
     * Cannot use the extra_info keyword to name the group of carts.
     *
     * @testdox Cannot use the extra_info keyword to name the group of carts.
     * @test
     */
    public function cannot_use_the_extra_info_keyword_to_name_the_group_of_carts()
    {
        $this->assertException(InvalidCartNameException::class);

        $cart = $this->initCart()->name('extra_info.shopping');
    }

    /**
     * Can check if the current cart is grouped or not using the hasBeenGrouped() method.
     *
     * @testdox Can check if the current cart is grouped or not using the hasBeenGrouped() method.
     * @test
     */
    public function can_check_if_the_current_cart_is_grouped_or_not_using_the_hasBeenGrouped_method()
    {
        $cart1 = $this->initCart()->name('cart1');
        $cart2 = $this->initCart()->name('grouped.cart2');

        $this->assertFalse($cart1->hasBeenGrouped());
        $this->assertTrue($cart2->hasBeenGrouped());
    }

    /**
     * Can use different cart names with one instance.
     *
     * @testdox Can use different cart names with one instance.
     * @test
     */
    public function can_use_different_cart_names_with_one_instance()
    {
        $cart  = $this->initCart();
        $cart1 = $cart->name('shopping');
        $cart2 = $cart->name('recently_viewed');

        $this->assertEquals($cart1, $cart2);
    }

    /**
     * Can create different cart instances.
     *
     * @testdox Can create different cart instances.
     * @test
     */
    public function can_create_different_cart_instances()
    {
        $cart  = $this->initCart();
        $cart1 = $cart->newInstance('shopping');
        $cart2 = $cart->newInstance('recently_viewed');

        $this->assertNotEquals($cart1, $cart2);
    }

    /**
     * Different cart instances may share the same cart name.
     *
     * @testdox Different cart instances may share the same cart name.
     * @test
     */
    public function different_cart_instances_may_share_the_same_cart_name()
    {
        $cart  = $this->initCart();
        $cart1 = $cart->newInstance('shopping');
        $cart2 = $cart->newInstance('recently_viewed');

        $cart2->name('shopping');
        $this->assertEquals($cart1, $cart2);

        $cart2->name('wishlist');
        $this->assertNotEquals($cart1, $cart2);
    }

    /**
     * Cart can be used for commercial purposes or not based on Laravel configuration file.
     *
     * @testdox Cart can be used for commercial purposes or not based on Laravel configuration file.
     * @test
     */
    public function cart_can_be_used_for_commercial_purposes_or_not_based_on_laravel_configuration_file()
    {
        $cart  = $this->initCart();
        $cart1 = $cart->newInstance('shopping');        // is commercial cart
        $cart2 = $cart->newInstance('recently_viewed'); // none commercial cart, cause of listing in config

        $this->assertTrue($cart1->isCommercialCart());
        $this->assertFalse($cart2->isCommercialCart());
    }

    /**
     * Cart can use built-in tax system or not based on Laravel configuration file.
     *
     * @testdox Cart can use built-in tax system or not based on Laravel configuration file.
     * @test
     */
    public function cart_can_use_built_in_tax_system_or_not_based_on_laravel_configuration_file()
    {
        $config       = app('config');
        $configStatus = $config->get('cart.use_builtin_tax', true);
        $cart         = $this->initCart();

        $config->set('cart.use_builtin_tax', !$configStatus);
        $cart1 = $cart->newInstance('cart1');
        $this->assertEquals(!$configStatus, $cart1->isEnabledBuiltinTax());

        $config->set('cart.use_builtin_tax', $configStatus);
        $cart2 = $cart->newInstance('cart2');
        $this->assertEquals($configStatus, $cart2->isEnabledBuiltinTax());
    }

    /**
     * Can set useForCommercial status on the fly if the cart is empty.
     *
     * @testdox Can set useForCommercial status on the fly if the cart is empty.
     * @test
     */
    public function can_set_useForCommercial_status_on_the_fly_if_the_cart_is_empty()
    {
        $cart = $this->initCart();

        $cart->useForCommercial();
        $this->assertTrue($cart->isCommercialCart());

        $cart->useForCommercial(false);
        $this->assertFalse($cart->isCommercialCart());
    }

    /**
     * Cannot set useForCommercial status on the fly if the cart is not empty.
     *
     * @testdox Cannot set useForCommercial status on the fly if the cart is not empty.
     * @test
     */
    public function cannot_set_useForCommercial_status_on_the_fly_if_the_cart_is_not_empty()
    {
        $cart = $this->initCart();

        $cart->useForCommercial();
        $this->assertTrue($cart->isCommercialCart());

        $cart->addItem([
            'id'    => 1,
            'title' => 'Example item title'
        ]);

        $cart->useForCommercial(false);
        $this->assertTrue($cart->isCommercialCart());
    }

    /**
     * Can set useBuiltinTax status on the fly if the cart is empty.
     *
     * @testdox Can set useBuiltinTax status on the fly if the cart is empty.
     * @test
     */
    public function can_set_useBuiltinTax_status_on_the_fly_if_the_cart_is_empty()
    {
        $cart = $this->initCart();

        $cart->useBuiltinTax();
        $this->assertTrue($cart->isEnabledBuiltinTax());

        $cart->useBuiltinTax(false);
        $this->assertFalse($cart->isEnabledBuiltinTax());
    }

    /**
     * Cannot set useBuiltinTax status on the fly if the cart is not empty.
     *
     * @testdox Cannot set useBuiltinTax status on the fly if the cart is not empty.
     * @test
     */
    public function cannot_set_useBuiltinTax_status_on_the_fly_if_the_cart_is_not_empty()
    {
        $cart = $this->initCart();

        $cart->useBuiltinTax();
        $this->assertTrue($cart->isEnabledBuiltinTax());

        $cart->addItem([
            'id'    => 1,
            'title' => 'Example item title'
        ]);

        $cart->useBuiltinTax(false);
        $this->assertTrue($cart->isEnabledBuiltinTax());
    }

    /**
     * A non-commercial cart will be disabled built-in tax system.
     *
     * @testdox A non-commercial cart will be disabled built-in tax system.
     * @test
     */
    public function a_non_commercial_cart_will_be_a_disabled_builtin_tax_system()
    {
        $cart = $this->initCart();

        $cart->useForCommercial(true);
        $cart->useBuiltinTax(true);
        $this->assertTrue($cart->isEnabledBuiltinTax());

        $cart->useForCommercial(false);
        $cart->useBuiltinTax(true);
        $this->assertFalse($cart->isEnabledBuiltinTax());
    }

    /**
     * Can retrieve the subtotal amount of the cart as a number.
     *
     * @testdox Can retrieve the subtotal amount of the cart as a number.
     * @test
     */
    public function can_retrieve_the_subtotal_amount_of_the_cart_as_a_number()
    {
        $cart = $this->initCart();

        $cartSubtotal = $cart->getSubtotal();
        $this->assertTrue(is_float($cartSubtotal) || is_double($cartSubtotal) || is_integer($cartSubtotal));

        $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item title',
            'quantity' => 5,
            'price'    => 100
        ]);

        $cartSubtotal = $cart->getSubtotal();
        $this->assertTrue(is_float($cartSubtotal) || is_double($cartSubtotal) || is_integer($cartSubtotal));
    }

    /**
     * A non-commercial cart always has the subtotal and total amount is 0.
     *
     * @testdox A non-commercial cart always has the subtotal and total amount is 0.
     * @test
     */
    public function a_non_commercial_cart_always_has_the_subtotal_and_total_amount_is_0()
    {
        $cart     = $this->initCart()->useForCommercial(false);
        $id       = 123;
        $title    = 'Example title';
        $quantity = 5;
        $price    = 100;

        $addedItem = $cart->addItem([
            'id'       => $id,
            'title'    => $title,
            'quantity' => $quantity,
            'price'    => $price
        ]);

        $this->assertEquals(0, $cart->getSubtotal());
        $this->assertEquals(0, $cart->getTotal());
    }

    /**
     * Can retrieve the total amount of the cart as a number.
     *
     * @testdox Can retrieve the total amount of the cart as a number.
     * @test
     */
    public function can_retrieve_the_total_amount_of_the_cart_as_a_number()
    {
        $cart = $this->initCart();

        $cartTotal = $cart->getTotal();
        $this->assertTrue(is_float($cartTotal) || is_double($cartTotal) || is_integer($cartTotal));

        $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item title',
            'quantity' => 5,
            'price'    => 100
        ]);

        $cartTotal = $cart->getTotal();
        $this->assertTrue(is_float($cartTotal) || is_double($cartTotal) || is_integer($cartTotal));
    }

    /**
     * Can retrieve details information of the cart as Laravel collection.
     *
     * @testdox Can retrieve details information of the cart as Laravel collection.
     * @test
     */
    public function can_retrieve_details_information_of_the_cart_as_laravel_collection()
    {
        $cart = $this->initCart();

        $this->assertInstanceOf(Collection::class, $cart->getDetails());

        $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item title',
            'quantity' => 5,
            'price'    => 100
        ]);

        $this->assertInstanceOf(Collection::class, $cart->getDetails());
    }

    /**
     * Can retrieve details information of the carts group as Laravel collection.
     *
     * @testdox Can retrieve details information of the carts group as Laravel collection.
     * @test
     */
    public function can_retrieve_details_information_of_the_carts_group_as_laravel_collection()
    {
        $cart = $this->initCart()->name('demo_group.demo_cart');

        $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item title',
            'quantity' => 5,
            'price'    => 100
        ]);

        $cartDetails  = $cart->getDetails();
        $groupDetails = $cart->getGroupDetails();

        $this->assertInstanceOf(Collection::class, $groupDetails);
        $this->assertEquals($cartDetails, $groupDetails->get('subsections')->get('demo_cart'));
    }

    /**
     * Can set extended information for the cart.
     *
     * @testdox Can set extended information for the cart.
     * @test
     */
    public function can_set_extended_information_for_the_cart()
    {
        $cart = $this->initCart();

        $cart->setExtraInfo('demo_extra_info_1', 'Demo value 1');
        $cart->setExtraInfo('demo_extra_info_2', 'Demo value 2');

        $extraInfo = $cart->getExtraInfo();

        $this->assertTrue(is_array($extraInfo));
        $this->assertSimilarArray([
            'demo_extra_info_1' => 'Demo value 1',
            'demo_extra_info_2' => 'Demo value 2',
        ], $extraInfo);
    }

    /**
     * Can set extended information for the group of carts.
     *
     * @testdox Can set extended information for the group of carts.
     * @test
     */
    public function can_set_extended_information_for_the_group_of_carts()
    {
        $cart = $this->initCart()->name('shopping.shop');

        $cart->setGroupExtraInfo('shopping', 'demo_extra_info_1', 'Demo value 1');
        $cart->setGroupExtraInfo('shopping', 'demo_extra_info_2', 'Demo value 2');

        $extraInfo = $cart->getGroupExtraInfo('shopping');

        $this->assertTrue(is_array($extraInfo));
        $this->assertSimilarArray([
            'demo_extra_info_1' => 'Demo value 1',
            'demo_extra_info_2' => 'Demo value 2'
        ], $extraInfo);
    }

    /**
     * Can add item to cart only with the valid id and title arguments.
     *
     * @testdox Can add item to cart only with the valid id and title arguments.
     * @test
     */
    public function can_add_item_to_cart_only_with_the_valid_id_and_title_arguments()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'    => 123,
            'title' => 'Example item title'
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart only with the valid id, title and quantity arguments.
     *
     * @testdox Can add item to cart only with the valid id, title and quantity arguments.
     * @test
     */
    public function can_add_item_to_cart_only_with_the_valid_id_title_and_quantity_arguments()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'       => 123,
            'title'    => 'Example item title',
            'quantity' => 5
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart only with the valid id, title, quantity and price arguments.
     *
     * @testdox Can add item to cart only with the valid id, title, quantity and price arguments.
     * @test
     */
    public function can_add_item_to_cart_only_with_the_valid_id_title_quantity_and_price_arguments()
    {
        $cart      = $this->initCart();
        $id        = 123;
        $title     = 'Example title';
        $quantity  = 5;
        $price     = 100;
        $addedItem = $cart->addItem([
            'id'       => $id,
            'title'    => $title,
            'quantity' => $quantity,
            'price'    => $price
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart only with the valid id, title, quantity, price and options arguments.
     *
     * @testdox Can add item to cart only with the valid id, title, quantity, price and options arguments.
     * @test
     */
    public function can_add_item_to_cart_only_with_the_valid_id_title_quantity_price_and_options_arguments()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'       => 123,
            'title'    => 'Example item title',
            'quantity' => 5,
            'price'    => 100,
            'options'  => [
                'size' => 'XL'
            ]
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart only with the valid id, title, quantity, price, options and extra_info arguments.
     *
     * @testdox Can add item to cart only with the valid id, title, quantity, price, options and extra_info arguments.
     * @test
     */
    public function can_add_item_to_cart_only_with_the_valid_id_title_quantity_price_options_and_extra_info_arguments()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'         => 123,
            'title'      => 'Example item title',
            'quantity'   => 5,
            'price'      => 100,
            'options'    => ['size' => 'XL'],
            'extra_info' => ['description' => 'Example extra information']
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart with all valid arguments.
     *
     * @testdox Can add item to cart with all valid arguments.
     * @test
     */
    public function can_add_item_to_cart_with_all_valid_arguments()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'         => 123,
            'title'      => 'Example item title',
            'quantity'   => 5,
            'price'      => 100,
            'options'    => ['size' => 'XL'],
            'extra_info' => ['description' => 'Example extra information'],
            'taxable'    => false
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart only with an UseCartable model.
     *
     * @testdox Can add item to cart only with an UseCartable model.
     * @test
     */
    public function can_add_item_to_cart_only_with_an_UseCartable_model()
    {
        $cart        = $this->initCart();
        $useCartable = new UseCartableProduct;

        $this->assertInstanceOf(UseCartable::class, $useCartable);

        $addedItem = $cart->addItem(['model' => $useCartable]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart only with an UseCartable model and the valid quantity argument.
     *
     * @testdox Can add item to cart only with an UseCartable model and the valid quantity argument.
     * @test
     */
    public function can_add_item_to_cart_only_with_an_UseCartable_model_and_the_valid_quantity_argument()
    {
        $cart        = $this->initCart();
        $useCartable = new UseCartableProduct;

        $this->assertInstanceOf(UseCartable::class, $useCartable);

        $addedItem = $cart->addItem([
            'model'    => $useCartable,
            'quantity' => 5
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart only with an UseCartable model, the valid quantity and options arguments.
     *
     * @testdox Can add item to cart only with an UseCartable model, the valid quantity and options arguments.
     * @test
     */
    public function can_add_item_to_cart_only_with_an_UseCartable_model_the_valid_quantity_and_options_arguments()
    {
        $cart        = $this->initCart();
        $useCartable = new UseCartableProduct;

        $this->assertInstanceOf(UseCartable::class, $useCartable);

        $addedItem = $cart->addItem([
            'model'    => $useCartable,
            'quantity' => 5,
            'options'  => ['size' => 'XL']
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart only with an UseCartable model, the valid quantity, options and extra_info arguments.
     *
     * @testdox Can add item to cart only with an UseCartable model, the valid quantity, options and extra_info arguments.
     * @test
     */
    public function can_add_item_to_cart_only_with_an_UseCartable_model_the_valid_quantity_options_and_extra_info_arguments()
    {
        $cart        = $this->initCart();
        $useCartable = new UseCartableProduct;

        $this->assertInstanceOf(UseCartable::class, $useCartable);

        $addedItem = $cart->addItem([
            'model'      => $useCartable,
            'quantity'   => 5,
            'options'    => ['size' => 'XL'],
            'extra_info' => ['description' => 'Example extra information'],
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Can add item to cart only with an UseCartable model and all valid arguments.
     *
     * @testdox Can add item to cart only with an UseCartable model and all valid arguments.
     * @test
     */
    public function can_add_item_to_cart_with_an_UseCartable_model_and_all_valid_arguments()
    {
        $cart        = $this->initCart();
        $useCartable = new UseCartableProduct;

        $this->assertInstanceOf(UseCartable::class, $useCartable);

        $addedItem = $cart->addItem([
            'model'      => $useCartable,
            'quantity'   => 5,
            'options'    => ['size' => 'XL'],
            'extra_info' => ['description' => 'Example extra information'],
            'taxable'    => true
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
    }

    /**
     * Cannot add item to cart with invalid id argument.
     *
     * @testdox Cannot add item to cart with invalid id argument.
     * @test
     */
    public function cannot_add_item_to_cart_with_invalid_id_argument()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();

        $cart->addItem([
            'id'    => null,
            'title' => 'Example item title'
        ]);
    }

    /**
     * Cannot add item to cart with invalid title argument.
     *
     * @testdox Cannot add item to cart with invalid title argument.
     * @test
     */
    public function cannot_add_item_to_cart_with_invalid_title_argument()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();

        $cart->addItem([
            'id'    => 123,
            'title' => null
        ]);
    }

    /**
     * Cannot add item to cart with invalid quantity argument.
     *
     * @testdox Cannot add item to cart with invalid quantity argument.
     * @test
     */
    public function cannot_add_item_to_cart_with_invalid_quantity_argument()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();

        $cart->addItem([
            'id'       => 123,
            'title'    => 'Example item title',
            'quantity' => 'Invalid value'
        ]);
    }

    /**
     * Cannot add item to cart with invalid price argument.
     *
     * @testdox Cannot add item to cart with invalid price argument.
     * @test
     */
    public function cannot_add_item_to_cart_with_invalid_price_argument()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();

        $cart->addItem([
            'id'    => 123,
            'title' => 'Example item title',
            'price' => 'Invalid value'
        ]);
    }

    /**
     * Cannot add item to cart with invalid options argument.
     *
     * @testdox Cannot add item to cart with invalid options argument.
     * @test
     */
    public function cannot_add_item_to_cart_with_invalid_options_argument()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();

        $cart->addItem([
            'id'      => 123,
            'title'   => 'Example item title',
            'options' => 'Invalid value'
        ]);
    }

    /**
     * Cannot add item to cart with invalid extra_info argument.
     *
     * @testdox Cannot add item to cart with invalid extra_info argument.
     * @test
     */
    public function cannot_add_item_to_cart_with_invalid_extra_info_argument()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();

        $cart->addItem([
            'id'         => 123,
            'title'      => 'Example item title',
            'extra_info' => 'Invalid value'
        ]);
    }

    /**
     * Cannot add item to cart with invalid taxable argument.
     *
     * @testdox Cannot add item to cart with invalid taxable argument.
     * @test
     */
    public function cannot_add_item_to_cart_with_invalid_taxable_argument()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();

        $cart->addItem([
            'id'      => 123,
            'title'   => 'Example item title',
            'taxable' => 'Invalid value'
        ]);
    }

    /**
     * Can count the number of items in the cart as an integer number.
     *
     * @testdox Can count the number of items in the cart as an integer number.
     * @test
     */
    public function can_count_the_number_of_items_in_the_cart_as_an_integer_number()
    {
        $cart = $this->initCart();

        $this->assertTrue(is_integer($cart->countItems()));

        $cart->addItem([
            'id'       => 123,
            'title'    => 'Example item title',
            'quantity' => 5
        ]);

        $this->assertTrue(is_integer($cart->countItems()));
    }

    /**
     * Adding the same item to the cart more than once will only increase the quantities of that item in the cart.
     *
     * @testdox Adding the same item to the cart more than once will only increase the quantities of that item in the cart.
     * @test
     */
    public function adding_the_same_item_to_the_cart_more_than_once_will_only_increase_the_quantities_of_that_item_in_the_cart()
    {
        $cart = $this->initCart();

        $firstItem = $cart->addItem([
            'id'         => 123,
            'title'      => 'Example item title',
            'quantity'   => 5,
            'price'      => 100,
            'options'    => ['size' => 'XL'],
            'extra_info' => ['description' => 'Example extra information'],
            'taxable'    => false
        ]);

        $this->assertEquals(5, $firstItem->getQuantity());

        $secondItem = $cart->addItem([
            'id'         => 123,
            'title'      => 'Example item title',
            'quantity'   => 5,
            'price'      => 100,
            'options'    => ['size' => 'XL'],
            'extra_info' => ['description' => 'Example extra information'],
            'taxable'    => false
        ]);

        $this->assertEquals(10, $secondItem->getQuantity());
        $this->assertEquals($firstItem, $secondItem);

        $this->assertEquals(1, $cart->countItems(), 'Cart should have 1 item.');
        $this->assertEquals(10, $cart->sumItemsQuantity(), 'Cart should have total of 10 item quantites.');
    }

    /**
     * Can calculate the quantities of all items in the cart using the sumItemsQuantity() method.
     *
     * @testdox Can calculate the quantities of all items in the cart using the sumItemsQuantity() method.
     * @test
     */
    public function can_calculate_the_sum_quantities_of_all_items_in_the_cart_using_the_sumItemsQuantity_method()
    {
        $cart = $this->initCart();

        $this->assertTrue(is_integer($cart->sumItemsQuantity()));

        $cart->addItem([
            'id'       => 123,
            'title'    => 'Example item title',
            'quantity' => 5,
        ]);

        $this->assertTrue(is_integer($cart->sumItemsQuantity()));
    }

    /**
     * Can calculate the sum subtotals of all items in the cart using the getItemsSubtotal() method.
     *
     * @testdox Can calculate the sum subtotals of all items in the cart using the getItemsSubtotal() method.
     * @test
     */
    public function can_calculate_the_sum_subtotals_of_all_items_in_the_cart_using_the_getItemsSubtotal_method()
    {
        $cart = $this->initCart();

        $itemsSubtotal = $cart->getItemsSubtotal();
        $this->assertTrue(is_float($itemsSubtotal) || is_double($itemsSubtotal) || is_integer($itemsSubtotal));

        $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item title',
            'quantity' => 5,
            'price'    => 100
        ]);

        $itemsSubtotal = $cart->getItemsSubtotal();
        $this->assertTrue(is_float($itemsSubtotal) || is_double($itemsSubtotal) || is_integer($itemsSubtotal));
    }

    /**
     * Can retrieve an added item using the getItem() method with a given hash code.
     *
     * @testdox Can retrieve an added item using the getItem() method with a given hash code.
     * @test
     */
    public function can_retrieve_an_added_item_using_the_getItem_method_with_a_given_hash_code()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example item title',
        ]);
        $gettedItem = $cart->getItem($item->getHash());

        $this->assertEquals($item, $gettedItem);
    }

    /**
     * Can get an array of added items using the getItems() method.
     *
     * @testdox Can get an array of added items using the getItems() method.
     * @test
     */
    public function can_get_an_array_of_added_items_using_the_getItems_method()
    {
        $cart  = $this->initCart();
        $item1 = $cart->addItem([
            'id'    => 1,
            'title' => 'Example item title 1',
        ]);
        $item2 = $cart->addItem([
            'id'    => 2,
            'title' => 'Example item title 2',
        ]);
        $addedItems = $cart->getItems();

        $this->assertTrue(is_array($addedItems));
        $this->assertEquals($cart->countItems(), count($addedItems));
        $this->assertEquals($item1, $addedItems[$item1->getHash()]);
        $this->assertEquals($item2, $addedItems[$item2->getHash()]);
    }

    /**
     * Can remove an added item using the removeItem() method with a given hash code.
     *
     * @testdox Can remove an added item using the removeItem() method with a given hash code.
     * @test
     */
    public function can_remove_an_added_item_using_the_removeItem_method_with_a_given_hash_code()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example item title',
        ]);

        $cart->removeItem($item->getHash());
        $this->assertEquals(0, $cart->countItems());
    }

    /**
     * Can remove all items in the cart using the clearItems() method.
     *
     * @testdox Can remove all items in the cart using the clearItems() method.
     * @test
     */
    public function can_remove_all_items_in_the_cart_using_the_clearItems_method()
    {
        $cart = $this->initCart();

        $cart->addItem(['id' => 1, 'title' => 'Example item title 1']);
        $cart->addItem(['id' => 2, 'title' => 'Example item title 2']);

        $this->assertEquals(2, $cart->countItems());
        $cart->clearItems();
        $this->assertEquals(0, $cart->countItems());
    }

    /**
     * Can apply a tax to the cart with the minimum of valid attributes.
     *
     * @testdox Can apply a tax to the cart with the minimum of valid attributes.
     * @test
     */
    public function can_apply_a_tax_to_the_cart_with_the_minimum__of_valid_attributes()
    {
        $cart = $this->initCart();
        $tax  = $cart->applyTax([
            'id'    => 1,
            'title' => 'Demo tax',
        ]);

        $this->assertInstanceOf(Tax::class, $tax);
    }

    /**
     * Applying a tax to the cart has disabled built-in tax system is ineffective.
     *
     * @testdox Applying a tax to the cart has disabled built-in tax system is ineffective.
     * @test
     */
    public function applying_a_tax_to_the_cart_has_disabled_builtin_tax_system_is_ineffective()
    {
        $cart = $this->initCart()->useBuiltinTax(false);

        $this->assertEquals(0, $cart->countTaxes());

        $tax = $cart->applyTax([
            'id'    => 1,
            'title' => 'Demo tax',
        ]);

        $this->assertEquals(null, $tax);
        $this->assertEquals(0, $cart->countTaxes());
    }

    /**
     * Cannot apply a tax to the cart with invalid id attribute.
     *
     * @testdox Cannot apply a tax to the cart with invalid id attribute.
     * @test
     */
    public function cannot_apply_a_tax_to_the_cart_with_invalid_id_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $tax  = $cart->applyTax([
            'id'    => null,
            'title' => 'Demo tax',
        ]);
    }

    /**
     * Cannot apply a tax to the cart with invalid title attribute.
     *
     * @testdox Cannot apply a tax to the cart with invalid title attribute.
     * @test
     */
    public function cannot_apply_a_tax_to_the_cart_with_invalid_title_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $tax  = $cart->applyTax([
            'id'    => 1,
            'title' => null,
        ]);
    }

    /**
     * Cannot apply a tax to the cart with invalid rate attribute.
     *
     * @testdox Cannot apply a tax to the cart with invalid rate attribute.
     * @test
     */
    public function cannot_apply_a_tax_to_the_cart_with_invalid_rate_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $tax  = $cart->applyTax([
            'id'    => 1,
            'title' => 'Demo tax',
            'rate'  => 'invalid rate'
        ]);
    }

    /**
     * Cannot apply a tax to the cart with invalid extra_info attribute.
     *
     * @testdox Cannot apply a tax to the cart with invalid extra_info attribute.
     * @test
     */
    public function cannot_apply_a_tax_to_the_cart_with_invalid_extra_info_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $tax  = $cart->applyTax([
            'id'         => 1,
            'title'      => 'Demo tax',
            'rate'       => 10,
            'extra_info' => 'invalid extra info'
        ]);
    }

    /**
     * Can count number of taxes that has been applied to the cart by the countTaxes() method.
     *
     * @testdox Can count number of taxes that has been applied to the cart by the countTaxes() method.
     * @test
     */
    public function can_count_number_of_taxes_that_has_been_applied_to_the_cart_by_the_countTaxes_method()
    {
        $cart = $this->initCart();

        $this->assertEquals(0, $cart->countTaxes());

        $tax1 = $cart->applyTax(['id' => 1, 'title' => 'Demo tax 1']);

        $this->assertInstanceOf(Tax::class, $tax1);
        $this->assertEquals(1, $cart->countTaxes());

        $tax2 = $cart->applyTax(['id' => 2, 'title' => 'Demo tax 2']);

        $this->assertInstanceOf(Tax::class, $tax2);
        $this->assertEquals(2, $cart->countTaxes());
    }

    /**
     * Can retrieve an applied tax using the getTax() method with a given hash code.
     *
     * @testdox Can retrieve an applied tax using the getTax() method with a given hash code.
     * @test
     */
    public function can_retrieve_an_applied_tax_using_the_getTax_method_with_a_given_hash_code()
    {
        $cart      = $this->initCart();
        $tax       = $cart->applyTax(['id' => 1, 'title' => 'Demo tax']);
        $gettedTax = $cart->getTax($tax->getHash());

        $this->assertEquals($tax, $gettedTax);
    }

    /**
     * Can get an array of applied taxes using the getTaxes() method.
     *
     * @testdox Can get an array of applied taxes using the getTaxes() method.
     * @test
     */
    public function can_get_an_array_of_applied_taxes_using_the_getTaxes_method()
    {
        $cart         = $this->initCart();
        $tax1         = $cart->applyTax(['id' => 1, 'title' => 'Demo tax 1']);
        $tax2         = $cart->applyTax(['id' => 2, 'title' => 'Demo tax 2']);
        $appliedTaxes = $cart->getTaxes();

        $this->assertTrue(is_array($appliedTaxes));
        $this->assertEquals($cart->countTaxes(), count($appliedTaxes));
        $this->assertEquals($tax1, $appliedTaxes[$tax1->getHash()]);
        $this->assertEquals($tax2, $appliedTaxes[$tax2->getHash()]);
    }

    /**
     * Can update an applied tax using the updateTax() method with a given hash code.
     *
     * @testdox Can update an applied tax using the updateTax() method with a given hash code.
     * @test
     */
    public function can_update_an_applied_tax_using_the_updateTax_method_with_a_given_hash_code()
    {
        $cart = $this->initCart();
        $tax  = $cart->applyTax([
            'id'    => 1,
            'title' => 'Demo tax',
        ]);
        $updatedTax = $cart->updateTax($tax->getHash(), [
            'title' => 'Updated title'
        ]);

        $this->assertEquals($tax, $updatedTax);
        $this->assertEquals('Updated title', $tax->getTitle());
    }

    /**
     * Can remove an applied tax using the removeTax() method with a given hash code.
     *
     * @testdox Can remove an applied tax using the removeTax() method with a given hash code.
     * @test
     */
    public function can_remove_an_applied_tax_using_the_removeTax_method_with_a_given_hash_code()
    {
        $cart = $this->initCart();
        $tax  = $cart->applyTax([
            'id'    => 1,
            'title' => 'Demo tax',
        ]);

        $cart->removeTax($tax->getHash());
        $this->assertEquals(0, $cart->countTaxes());
    }

    /**
     * Can remove all applied taxes using the clearTaxes() method.
     *
     * @testdox Can remove all applied taxes using the clearTaxes() method.
     * @test
     */
    public function can_remove_all_applied_taxes_using_the_clearTaxes_method()
    {
        $cart = $this->initCart();

        $cart->applyTax(['id' => 1, 'title' => 'Demo tax 1']);
        $cart->applyTax(['id' => 2, 'title' => 'Demo tax 2']);

        $this->assertEquals(2, $cart->countTaxes());
        $cart->clearTaxes();
        $this->assertEquals(0, $cart->countTaxes());
    }

    /**
     * Can apply an action to the cart with the minimum of valid attributes.
     *
     * @testdox Can apply an action to the cart with the minimum of valid attributes.
     * @test
     */
    public function can_apply_an_action_to_the_cart_with_the_minimum_of_valid_attributes()
    {
        $cart = $this->initCart();

        $action = $cart->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
        ]);

        $this->assertInstanceOf(Action::class, $action);
    }

    /**
     * Applying an action to the non-commercial cart is ineffective.
     *
     * @testdox Applying an action to the non-commercial cart is ineffective.
     * @test
     */
    public function applying_an_action_to_the_non_commercial_cart_is_ineffective()
    {
        $cart = $this->initCart()->useForCommercial(false);

        $this->assertEquals(0, $cart->countActions());

        $action = $cart->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
        ]);

        $this->assertEquals(null, $action);
        $this->assertEquals(0, $cart->countActions());
    }

    /**
     * Cannot apply an action to the cart with invalid id attribute.
     *
     * @testdox Cannot apply an action to the cart with invalid id attribute.
     * @test
     */
    public function cannot_apply_an_action_to_the_cart_with_invalid_id_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart   = $this->initCart();
        $action = $cart->applyAction([
            'id'    => null,
            'title' => 'Demo action',
        ]);
    }

    /**
     * Cannot apply an action to the cart with invalid title attribute.
     *
     * @testdox Cannot apply an action to the cart with invalid title attribute.
     * @test
     */
    public function cannot_apply_an_action_to_the_cart_with_invalid_title_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart   = $this->initCart();
        $action = $cart->applyAction([
            'id'    => 1,
            'title' => null,
        ]);
    }

    /**
     * Cannot apply an action to the cart with invalid value attribute.
     *
     * @testdox Cannot apply an action to the cart with invalid value attribute.
     * @test
     */
    public function cannot_apply_an_action_to_the_cart_with_invalid_value_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart   = $this->initCart();
        $action = $cart->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
            'value' => 'invalid value'
        ]);
    }

    /**
     * Cannot apply an action to the cart with invalid group attribute.
     *
     * @testdox Cannot apply an action to the cart with invalid group attribute.
     * @test
     */
    public function cannot_apply_an_action_to_the_cart_with_invalid_group_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart   = $this->initCart();
        $action = $cart->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
            'value' => '-10%',
            'group' => null
        ]);
    }

    /**
     * Cannot apply an action to the cart with invalid rules attribute.
     *
     * @testdox Cannot apply an action to the cart with invalid rules attribute.
     * @test
     */
    public function cannot_apply_an_action_to_the_cart_with_invalid_rules_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart   = $this->initCart();
        $action = $cart->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
            'value' => '-10%',
            'group' => 'discount',
            'rules' => 'invalid rules'
        ]);
    }

    /**
     * Cannot apply an action to the cart with invalid extra_info attribute.
     *
     * @testdox Cannot apply an action to the cart with invalid extra_info attribute.
     * @test
     */
    public function cannot_apply_an_action_to_the_cart_with_invalid_extra_info_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart   = $this->initCart();
        $action = $cart->applyAction([
            'id'         => 1,
            'title'      => 'Demo action',
            'value'      => '-10%',
            'group'      => 'discount',
            'rules'      => [],
            'extra_info' => 'invalid extra_info'
        ]);
    }

    /**
     * Can count number of actions that has been applied to the cart by the countActions() method.
     *
     * @testdox Can count number of actions that has been applied to the cart by the countActions() method.
     * @test
     */
    public function can_count_number_of_actions_that_has_been_applied_to_the_cart_by_the_countActions_method()
    {
        $cart = $this->initCart();

        $this->assertEquals(0, $cart->countActions());

        $action1 = $cart->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
        ]);

        $this->assertInstanceOf(Action::class, $action1);
        $this->assertEquals(1, $cart->countActions());

        $action2 = $cart->applyAction([
            'id'    => 2,
            'title' => 'Demo action 2',
        ]);

        $this->assertInstanceOf(Action::class, $action2);
        $this->assertEquals(2, $cart->countActions());
    }

    /**
     * Can retrieve an applied action using the getAction() method with a given hash code.
     *
     * @testdox Can retrieve an applied action using the getAction() method with a given hash code.
     * @test
     */
    public function can_retrieve_an_applied_action_using_the_getAction_method_with_a_given_hash_code()
    {
        $cart         = $this->initCart();
        $action       = $cart->applyAction(['id' => 1, 'title' => 'Demo action']);
        $gettedAction = $cart->getAction($action->getHash());

        $this->assertEquals($action, $gettedAction);
    }

    /**
     * Can get an array of applied actions using the getActions() method.
     *
     * @testdox Can get an array of applied actions using the getActions() method.
     * @test
     */
    public function can_get_an_array_of_applied_actions_using_the_getActions_method()
    {
        $cart           = $this->initCart();
        $action1        = $cart->applyAction(['id' => 1, 'title' => 'Demo action']);
        $action2        = $cart->applyAction(['id' => 2, 'title' => 'Demo action 2']);
        $appliedActions = $cart->getActions();

        $this->assertTrue(is_array($appliedActions));
        $this->assertEquals($cart->countActions(), count($appliedActions));
        $this->assertEquals($action1, $appliedActions[$action1->getHash()]);
        $this->assertEquals($action2, $appliedActions[$action2->getHash()]);
    }

    /**
     * Can update an applied action using the updateAction() method with a given hash code.
     *
     * @testdox Can update an applied action using the updateAction() method with a given hash code.
     * @test
     */
    public function can_update_an_applied_action_using_the_updateAction_method_with_a_given_hash_code()
    {
        $cart   = $this->initCart();
        $action = $cart->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
        ]);
        $updatedAction = $cart->updateAction($action->getHash(), [
            'title' => 'Updated title'
        ]);

        $this->assertEquals($action, $updatedAction);
        $this->assertEquals('Updated title', $action->getTitle());
    }

    /**
     * Can remove an applied action using the removeAction() method with a given hash code.
     *
     * @testdox Can remove an applied action using the removeAction() method with a given hash code.
     * @test
     */
    public function can_remove_an_applied_action_using_the_removeAction_method_with_a_given_hash_code()
    {
        $cart   = $this->initCart();
        $action = $cart->applyAction(['id' => 1, 'title' => 'Demo action']);

        $cart->removeAction($action->getHash());
        $this->assertEquals(0, $cart->countActions());
    }

    /**
     * Can remove all applied actions using the clearActions() method.
     *
     * @testdox Can remove all applied actions using the clearActions() method.
     * @test
     */
    public function can_remove_all_applied_actions_using_the_clearActions_method()
    {
        $cart = $this->initCart();

        $cart->applyAction(['id' => 1, 'title' => 'Demo action 1']);
        $cart->applyAction(['id' => 2, 'title' => 'Demo action 2']);

        $this->assertEquals(2, $cart->countActions());
        $cart->clearActions();
        $this->assertEquals(0, $cart->countActions());
    }

    /**
     * Can calculate the sum amount of applied actions using the sumActionsAmount() method.
     *
     * @testdox Can calculate the sum amount of applied actions using the sumActionsAmount() method.
     * @test
     */
    public function can_calculate_the_sum_amount_of_applied_actions_using_the_sumActionsAmount_method()
    {
        $cart    = $this->initCart();
        $action1 = $cart->applyAction(['id' => 1, 'title' => 'Demo action 1', 'value' => -5000]);
        $action2 = $cart->applyAction(['id' => 2, 'title' => 'Demo action 2', 'value' => -5000]);

        $this->assertEquals(max((0 - $cart->getItemsSubtotal()), -10000), $cart->sumActionsAmount());
    }
}
