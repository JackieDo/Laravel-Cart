<?php

use Illuminate\Support\Collection;
use Jackiedo\Cart\Action;
use Jackiedo\Cart\Cart;
use Jackiedo\Cart\Contracts\UseCartable;
use Jackiedo\Cart\Exceptions\InvalidArgumentException;
use Jackiedo\Cart\Exceptions\InvalidAssociatedException;
use Jackiedo\Cart\Item;
use Orchestra\Testbench\TestCase;

require_once __DIR__ . '/Traits/CommonSetUp.php';

require_once __DIR__ . '/Mocks/UseCartableProduct.php';

/**
 * @internal
 *
 * @coversNothing
 */
class ItemTest extends TestCase
{
    use CommonSetUp;

    /**
     * The item attributes can be retrieved using a corresponding getter.
     *
     * @testdox The item attributes can be retrieved using a corresponding getter.
     *
     * @test
     */
    public function the_item_attributes_can_be_retrieved_using_a_corresponding_getter()
    {
        $cart      = $this->initCart();
        $id        = 123;
        $title     = 'Example title';
        $quantity  = 5;
        $price     = 100;
        $options   = ['size' => 'XL', 'color' => 'red'];
        $extraInfo = ['description' => 'Example extra information'];
        $taxable   = false;
        $addedItem = $cart->addItem([
            'id'         => $id,
            'title'      => $title,
            'quantity'   => $quantity,
            'price'      => $price,
            'options'    => $options,
            'extra_info' => $extraInfo,
            'taxable'    => $taxable,
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
        $this->assertEquals($id, $addedItem->getId());
        $this->assertEquals($title, $addedItem->getTitle());
        $this->assertEquals($quantity, $addedItem->getQuantity());
        $this->assertEquals($price, $addedItem->getPrice());
        $this->assertEquals($taxable, $addedItem->getTaxable());
        $this->assertSimilarArray($options, $addedItem->getOptions());
        $this->assertSimilarArray($extraInfo, $addedItem->getExtraInfo());
        $this->assertEquals($quantity * $price, $addedItem->getTotalPrice());
    }

    /**
     * The item attributes can also be retrieved by the get() method.
     *
     * @testdox The item attributes can also be retrieved by the get() method.
     *
     * @test
     */
    public function the_item_attributes_can_also_be_retrieved_by_the_get_method()
    {
        $cart      = $this->initCart();
        $id        = 123;
        $title     = 'Example title';
        $quantity  = 5;
        $price     = 100;
        $options   = ['size' => 'XL', 'color' => 'red'];
        $extraInfo = ['description' => 'Example extra information'];
        $taxable   = false;
        $addedItem = $cart->addItem([
            'id'         => $id,
            'title'      => $title,
            'quantity'   => $quantity,
            'price'      => $price,
            'options'    => $options,
            'extra_info' => $extraInfo,
            'taxable'    => $taxable,
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
        $this->assertEquals($id, $addedItem->get('id'));
        $this->assertEquals($title, $addedItem->get('title'));
        $this->assertEquals($quantity, $addedItem->get('quantity'));
        $this->assertEquals($price, $addedItem->get('price'));
        $this->assertEquals($taxable, $addedItem->get('taxable'));
        $this->assertSimilarArray($options, $addedItem->get('options'));
        $this->assertSimilarArray($extraInfo, $addedItem->get('extra_info'));
        $this->assertEquals($quantity * $price, $addedItem->get('total_price'));
    }

    /**
     * Can retrieve details of item as Laravel collection using the getDetails() method.
     *
     * @testdox Can retrieve details of item as Laravel collection using the getDetails() method.
     *
     * @test
     */
    public function can_retrieve_details_of_item_as_laravel_collection_using_the_get_details_method()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);

        $this->assertInstanceOf(Collection::class, $addedItem->getDetails());
    }

    /**
     * Can retrieve the cart instance that item belongs to using the getCart() method.
     *
     * @testdox Can retrieve the cart instance that item belongs to using the getCart() method.
     *
     * @test
     */
    public function can_retrieve_the_cart_instance_that_item_belongs_to_using_the_get_cart_method()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $cartOfItem = $addedItem->getCart();

        $this->assertInstanceOf(Cart::class, $cartOfItem);
        $this->assertEquals($cart, $cartOfItem);
    }

    /**
     * Can also retrieve the cart instance that item belongs to using the getParentNode() method.
     *
     * @testdox Can also retrieve the cart instance that item belongs to using the getParentNode() method.
     *
     * @test
     */
    public function can_also_retrieve_the_cart_instance_that_item_belongs_to_using_the_get_parent_node_method()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $cartOfItem = $addedItem->getParentNode();

        $this->assertInstanceOf(Cart::class, $cartOfItem);
        $this->assertEquals($cart, $cartOfItem);
    }

    /**
     * Each item always has a hash code that can be retrieved by the getHash() method.
     *
     * @testdox Each item always has a hash code that can be retrieved by the getHash() method.
     *
     * @test
     */
    public function each_item_always_has_a_hash_code_that_can_be_retrieved_by_the_get_hash_method()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);

        $this->assertTrue(method_exists($addedItem, 'getHash'));
        $this->assertEquals($addedItem->getHash(), $addedItem->get('hash'));
    }

    /**
     * Items with different id, price, options and associated_class attributes will have different hash codes.
     *
     * @testdox Items with different id, price, options and associated_class attributes will have different hash codes.
     *
     * @test
     */
    public function items_with_different_id_price_options_and_associated_class_attributes_will_have_different_hash_codes()
    {
        $cart       = $this->initCart();
        $addedItem1 = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $addedItem2 = $cart->addItem([
            'id'    => 321,
            'title' => 'Example title',
        ]);

        $this->assertInstanceOf(Item::class, $addedItem1);
        $this->assertInstanceOf(Item::class, $addedItem2);

        $this->assertNotEquals($addedItem1, $addedItem2);
        $this->assertNotEquals($addedItem1->getHash(), $addedItem2->getHash());
    }

    /**
     * Can retrieve associated_class model of item if it is instance of UseCartable before adding to cart.
     *
     * @testdox Can retrieve associated_class model of item if it is instance of UseCartable before adding to cart.
     *
     * @test
     */
    public function can_retrieve_associated_class_model_of_item_if_it_is_instance_of__use_cartable_before_adding_to_cart()
    {
        $cart        = $this->initCart();
        $useCartable = new UseCartableProduct();
        $addedItem   = $cart->addItem(['model' => $useCartable]);

        $this->assertEquals($useCartable, $addedItem->getModel());
    }

    /**
     * Cannot retrieve associated_class model of item if it is not instance of UseCartable before adding to cart.
     *
     * @testdox Cannot retrieve associated_class model of item if it is not instance of UseCartable before adding to cart.
     *
     * @test
     */
    public function cannot_retrieve_associated_class_model_of_item_if_it_is_not_instance_of__use_cartable_before_adding_to_cart()
    {
        $cart      = $this->initCart();
        $addedItem = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);

        $this->assertException(InvalidAssociatedException::class);

        $model = $addedItem->getModel();
    }

    /**
     * Can apply an action to the item with the minimum of valid attributes.
     *
     * @testdox Can apply an action to the item with the minimum of valid attributes.
     *
     * @test
     */
    public function can_apply_an_action_to_the_item_with_the_minimum_of_valid_attributes()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
        ]);

        $this->assertInstanceOf(Action::class, $action);
    }

    /**
     * Applying an action to the item in a non-commercial cart is ineffective.
     *
     * @testdox Applying an action to the item in a non-commercial cart is ineffective.
     *
     * @test
     */
    public function applying_an_action_to_the_item_in_a_non_commercial_cart_is_ineffective()
    {
        $cart = $this->initCart()->useForCommercial(false);
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);

        $this->assertEquals(0, $item->countActions());

        $action = $item->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
        ]);

        $this->assertEquals(null, $action);
        $this->assertEquals(0, $item->countActions());
    }

    /**
     * Cannot apply an action to the item with invalid id attribute.
     *
     * @testdox Cannot apply an action to the item with invalid id attribute.
     *
     * @test
     */
    public function cannot_apply_an_action_to_the_item_with_invalid_id_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'    => null,
            'title' => 'Demo action',
        ]);
    }

    /**
     * Cannot apply an action to the item with invalid title attribute.
     *
     * @testdox Cannot apply an action to the item with invalid title attribute.
     *
     * @test
     */
    public function cannot_apply_an_action_to_the_item_with_invalid_title_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'    => 1,
            'title' => null,
        ]);
    }

    /**
     * Cannot apply an action to the item with invalid value attribute.
     *
     * @testdox Cannot apply an action to the item with invalid value attribute.
     *
     * @test
     */
    public function cannot_apply_an_action_to_the_item_with_invalid_value_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
            'value' => 'invalid value',
        ]);
    }

    /**
     * Cannot apply an action to the item with invalid group attribute.
     *
     * @testdox Cannot apply an action to the item with invalid group attribute.
     *
     * @test
     */
    public function cannot_apply_an_action_to_the_item_with_invalid_group_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
            'value' => '-10%',
            'group' => null,
        ]);
    }

    /**
     * Cannot apply an action to the item with invalid rules attribute.
     *
     * @testdox Cannot apply an action to the item with invalid rules attribute.
     *
     * @test
     */
    public function cannot_apply_an_action_to_the_item_with_invalid_rules_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
            'value' => '-10%',
            'group' => 'discount',
            'rules' => 'invalid rules',
        ]);
    }

    /**
     * Cannot apply an action to the item with invalid extra_info attribute.
     *
     * @testdox Cannot apply an action to the item with invalid extra_info attribute.
     *
     * @test
     */
    public function cannot_apply_an_action_to_the_item_with_invalid_extra_info_attribute()
    {
        $this->assertException(InvalidArgumentException::class);

        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'         => 1,
            'title'      => 'Demo action',
            'value'      => '-10%',
            'group'      => 'discount',
            'rules'      => [],
            'extra_info' => 'invalid extra_info',
        ]);
    }

    /**
     * Can count number of actions that has been applied to the item by the countActions() method.
     *
     * @testdox Can count number of actions that has been applied to the item by the countActions() method.
     *
     * @test
     */
    public function can_count_number_of_actions_that_has_been_applied_to_the_item_by_the_count_actions_method()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);

        $this->assertEquals(0, $item->countActions());

        $action1 = $item->applyAction(['id' => 1, 'title' => 'Demo action 1']);

        $this->assertInstanceOf(Action::class, $action1);
        $this->assertEquals(1, $item->countActions());

        $action2 = $item->applyAction(['id' => 2, 'title' => 'Demo action 2']);

        $this->assertInstanceOf(Action::class, $action2);
        $this->assertEquals(2, $item->countActions());
    }

    /**
     * Can retrieve an applied action using the getAction() method with a given hash code.
     *
     * @testdox Can retrieve an applied action using the getAction() method with a given hash code.
     *
     * @test
     */
    public function can_retrieve_an_applied_action_using_the_get_action_method_with_a_given_hash_code()
    {
        $cart   = $this->initCart();
        $item   = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
        ]);
        $gettedAction = $item->getAction($action->getHash());

        $this->assertEquals($action, $gettedAction);
    }

    /**
     * Can get an array of applied actions using the getActions() method.
     *
     * @testdox Can get an array of applied actions using the getActions() method.
     *
     * @test
     */
    public function can_get_an_array_of_applied_actions_using_the_get_actions_method()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action1        = $item->applyAction(['id' => 1, 'title' => 'Demo action 1']);
        $action2        = $item->applyAction(['id' => 2, 'title' => 'Demo action 2']);
        $appliedActions = $item->getActions();

        $this->assertTrue(is_array($appliedActions));
        $this->assertEquals($item->countActions(), count($appliedActions));
        $this->assertEquals($action1, $appliedActions[$action1->getHash()]);
        $this->assertEquals($action2, $appliedActions[$action2->getHash()]);
    }

    /**
     * Can update an applied action using the updateAction() method with a given hash code.
     *
     * @testdox Can update an applied action using the updateAction() method with a given hash code.
     *
     * @test
     */
    public function can_update_an_applied_action_using_the_update_action_method_with_a_given_hash_code()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
        ]);
        $updatedAction = $item->updateAction($action->getHash(), [
            'title' => 'Updated title',
        ]);

        $this->assertEquals($action, $updatedAction);
        $this->assertEquals('Updated title', $action->getTitle());
    }

    /**
     * Can remove an applied action using the removeAction() method with a given hash code.
     *
     * @testdox Can remove an applied action using the removeAction() method with a given hash code.
     *
     * @test
     */
    public function can_remove_an_applied_action_using_the_remove_action_method_with_a_given_hash_code()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $action = $item->applyAction([
            'id'    => 1,
            'title' => 'Demo action',
        ]);

        $item->removeAction($action->getHash());
        $this->assertEquals(0, $item->countActions());
    }

    /**
     * Can remove all applied actions using the clearActions() method.
     *
     * @testdox Can remove all applied actions using the clearActions() method.
     *
     * @test
     */
    public function can_remove_all_applied_actions_using_the_clear_actions_method()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);

        $item->applyAction(['id' => 1, 'title' => 'Demo action 1']);
        $item->applyAction(['id' => 2, 'title' => 'Demo action 2']);

        $this->assertEquals(2, $item->countActions());
        $item->clearActions();
        $this->assertEquals(0, $item->countActions());
    }

    /**
     * Can calculate the sum amount of applied actions using the sumActionsAmount() method.
     *
     * @testdox Can calculate the sum amount of applied actions using the sumActionsAmount() method.
     *
     * @test
     */
    public function can_calculate_the_sum_amount_of_applied_actions_using_the_sum_actions_amount_method()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'       => 123,
            'title'    => 'Example title',
            'quantity' => 5,
            'price'    => 100,
        ]);
        $action1 = $item->applyAction(['id' => 1, 'title' => 'Demo action 1', 'value' => -5000]);
        $action2 = $item->applyAction(['id' => 2, 'title' => 'Demo action 2', 'value' => -5000]);

        $this->assertEquals(max(0 - $item->getTotalPrice(), -10000), $item->sumActionsAmount());
    }

    /**
     * Item in non-commercial carts always has quantity attribute is 1.
     *
     * @testdox Item in non-commercial carts always has quantity attribute is 1.
     *
     * @test
     */
    public function item_in_non_commercial_carts_always_has_quantity_attribute_is_1()
    {
        $cart      = $this->initCart()->useForCommercial(false);
        $quantity  = 5;
        $addedItem = $cart->addItem([
            'id'       => 123,
            'title'    => 'Example title',
            'quantity' => $quantity,
        ]);
        $itemQty = $addedItem->getQuantity();

        $this->assertInstanceOf(Item::class, $addedItem);
        $this->assertNotEquals($quantity, $itemQty);
        $this->assertEquals(1, $itemQty);
    }

    /**
     * Item in non-commercial carts always has price attribute is 0.
     *
     * @testdox Item in non-commercial carts always has price attribute is 0.
     *
     * @test
     */
    public function item_in_non_commercial_carts_always_has_price_attribute_is_0()
    {
        $cart      = $this->initCart()->useForCommercial(false);
        $price     = 100;
        $addedItem = $cart->addItem([
            'id'       => 123,
            'title'    => 'Example title',
            'quantity' => 5,
            'price'    => $price,
        ]);
        $itemPrice = $addedItem->getPrice();

        $this->assertInstanceOf(Item::class, $addedItem);
        $this->assertNotEquals($price, $itemPrice);
        $this->assertEquals(0, $itemPrice);
    }

    /**
     * Item in non-commercial carts always has taxable attribute is false.
     *
     * @testdox Item in non-commercial carts always has taxable attribute is false.
     *
     * @test
     */
    public function item_in_non_commercial_carts_always_has_taxable_attribute_is_false()
    {
        $cart      = $this->initCart()->useForCommercial(false);
        $addedItem = $cart->addItem([
            'id'      => 123,
            'title'   => 'Example title',
            'taxable' => true,
        ]);

        $this->assertInstanceOf(Item::class, $addedItem);
        $this->assertFalse($addedItem->getTaxable());
        $this->assertFalse($addedItem->isTaxable());
    }

    /**
     * Item in non-commercial carts always has options attribute is empty array.
     *
     * @testdox Item in non-commercial carts always has options attribute is empty array.
     *
     * @test
     */
    public function item_in_non_commercial_carts_always_has_options_attribute_is_empty_array()
    {
        $cart    = $this->initCart()->useForCommercial(false);
        $options = [
            'size'  => 'XL',
            'color' => 'red',
        ];
        $addedItem = $cart->addItem([
            'id'      => 123,
            'title'   => 'Example title',
            'options' => $options,
        ]);
        $itemOptions = $addedItem->getOptions();

        $this->assertInstanceOf(Item::class, $addedItem);
        $this->assertTrue(is_array($itemOptions));
        $this->assertEmpty($itemOptions);
    }

    /**
     * The total_price attribute of the item is calculated by the product of quantity and price.
     *
     * @testdox The total_price attribute of the item is calculated by the product of quantity and price.
     *
     * @test
     */
    public function the_total_price_attribute_of_the_item_is_calculated_by_the_product_of_quantity_and_price()
    {
        $cart  = $this->initCart();
        $cart1 = $cart->newInstance('cart1')->useForCommercial(true);
        $cart2 = $cart->newInstance('cart2')->useForCommercial(false);

        $id       = 123;
        $title    = 'Example title';
        $quantity = 5;
        $price    = 100;

        $addedItem1 = $cart1->addItem([
            'id'       => $id,
            'title'    => $title,
            'quantity' => $quantity,
            'price'    => $price,
        ]);
        $addedItem2 = $cart2->addItem([
            'id'       => $id,
            'title'    => $title,
            'quantity' => $quantity,
            'price'    => $price,
        ]);

        $this->assertEquals($addedItem1->getQuantity() * $addedItem1->getPrice(), $addedItem1->getTotalPrice());
        $this->assertEquals($addedItem2->getQuantity() * $addedItem2->getPrice(), $addedItem2->getTotalPrice());
    }

    /**
     * The subtotal attribute of the item is calculated by the sum of total_price and all actions amount.
     *
     * @testdox The subtotal attribute of the item is calculated by the sum of total_price and all actions amount.
     *
     * @test
     */
    public function the_subtotal_attribute_of_the_item_is_calculated_by_the_sum_of_total_price_and_all_actions_amount()
    {
        $cart  = $this->initCart();
        $cart1 = $cart->newInstance('cart1')->useForCommercial(true);
        $cart2 = $cart->newInstance('cart2')->useForCommercial(false);

        $id       = 123;
        $title    = 'Example title';
        $quantity = 5;
        $price    = 100;

        $addedItem1 = $cart1->addItem([
            'id'       => $id,
            'title'    => $title,
            'quantity' => $quantity,
            'price'    => $price,
        ]);
        $addedItem2 = $cart2->addItem([
            'id'       => $id,
            'title'    => $title,
            'quantity' => $quantity,
            'price'    => $price,
        ]);

        $this->assertEquals($addedItem1->getTotalPrice() + $addedItem1->sumActionsAmount(), $addedItem1->getSubtotal());
        $this->assertEquals($addedItem2->getTotalPrice() + $addedItem2->sumActionsAmount(), $addedItem2->getSubtotal());
    }

    /**
     * The detailed of item always has the hash, associated_class, id, title and extra_info attributes.
     *
     * @testdox The detailed of item always has the hash, associated_class, id, title and extra_info attributes.
     *
     * @test
     */
    public function the_detailed_of_item_always_has_the_hash_associated_class_id_title_and_extra_info_attributes()
    {
        $cart  = $this->initCart();
        $cart1 = $cart->newInstance('cart1')->useForCommercial(true);
        $cart2 = $cart->newInstance('cart2')->useForCommercial(false);

        $addedItem1   = $cart1->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $itemDetails1 = $addedItem1->getDetails();

        $addedItem2   = $cart2->addItem([
            'id'    => 123,
            'title' => 'Example title',
        ]);
        $itemDetails2 = $addedItem2->getDetails();

        $this->assertTrue($itemDetails1->has(['hash', 'associated_class', 'id', 'title', 'extra_info']));
        $this->assertTrue($itemDetails2->has(['hash', 'associated_class', 'id', 'title', 'extra_info']));
    }

    /**
     * The detailed of item in commercial cart will have more attributes quantity, price, total_price, subtotal, options and actions_amount.
     *
     * @testdox The detailed of item in commercial cart will have more attributes quantity, price, total_price, subtotal, options and actions_amount.
     *
     * @test
     */
    public function the_detailed_of_item_in_commercial_cart_will_have_more_attributes_quantity_price_total_price_subtotal_options_and_actions_amount()
    {
        $cart        = $this->initCart()->useForCommercial(true)->useBuiltinTax(false);
        $addedItem   = $cart->addItem([
            'id'       => 123,
            'title'    => 'Example title',
            'quantity' => 5,
            'price'    => 100,
        ]);
        $itemDetails = $addedItem->getDetails();

        $this->assertTrue($itemDetails->has([
            'hash',
            'associated_class',
            'id',
            'title',
            'extra_info',
            'quantity',
            'price',
            'total_price',
            'subtotal',
            'options',
            'actions_amount',
        ]));
    }

    /**
     * The detailed of item in taxable cart will have more attributes taxable and taxable_amount.
     *
     * @testdox The detailed of item in taxable cart will have more attributes taxable and taxable_amount.
     *
     * @test
     */
    public function the_detailed_of_item_in_taxable_cart_will_have_more_attributes_taxable_and_taxable_amount()
    {
        $cart        = $this->initCart()->useForCommercial(true)->useBuiltinTax(true);
        $addedItem   = $cart->addItem([
            'id'       => 123,
            'title'    => 'Example title',
            'quantity' => 5,
            'price'    => 100,
        ]);
        $itemDetails = $addedItem->getDetails();

        $this->assertTrue($itemDetails->has([
            'hash',
            'associated_class',
            'id',
            'title',
            'extra_info',
            'quantity',
            'price',
            'total_price',
            'subtotal',
            'options',
            'actions_amount',
            'taxable',
            'taxable_amount',
        ]));
    }
}
