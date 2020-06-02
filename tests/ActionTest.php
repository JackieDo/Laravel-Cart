<?php

use Illuminate\Support\Collection;
use Jackiedo\Cart\Action;
use Jackiedo\Cart\Cart;
use Jackiedo\Cart\Item;
use Orchestra\Testbench\TestCase;

require_once __DIR__ . '/Traits/CommonSetUp.php';

class ActionTest extends TestCase
{
    use CommonSetUp;

    /**
     * The action attributes can be retrieved using a corresponding getter.
     *
     * @testdox The action attributes can be retrieved using a corresponding getter.
     * @test
     */
    public function the_action_attributes_can_be_retrieved_using_a_corresponding_getter()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item',
            'quantity' => 1,
            'price'    => 1000
        ]);

        $id        = 1;
        $title     = 'Example action';
        $group     = 'discount';
        $value     = -100;
        $extraInfo = [
            'description' => 'Example extra information'
        ];

        $cartAction = $cart->applyAction([
            'id'         => $id,
            'title'      => $title,
            'group'      => $group,
            'value'      => $value,
            'extra_info' => $extraInfo
        ]);

        $itemAction = $item->applyAction([
            'id'         => $id,
            'title'      => $title,
            'group'      => $group,
            'value'      => $value,
            'extra_info' => $extraInfo
        ]);

        $this->assertInstanceOf(Action::class, $cartAction);
        $this->assertEquals($id, $cartAction->getId());
        $this->assertEquals($title, $cartAction->getTitle());
        $this->assertEquals($group, $cartAction->getGroup());
        $this->assertEquals($value, $cartAction->getValue());
        $this->assertSimilarArray($extraInfo, $cartAction->getExtraInfo());

        $this->assertInstanceOf(Action::class, $itemAction);
        $this->assertEquals($id, $itemAction->getId());
        $this->assertEquals($title, $itemAction->getTitle());
        $this->assertEquals($group, $itemAction->getGroup());
        $this->assertEquals($value, $itemAction->getValue());
        $this->assertSimilarArray($extraInfo, $itemAction->getExtraInfo());

        $this->assertNotEquals($cartAction, $itemAction);
    }

    /**
     * The action attributes can also be retrieved by the get() method.
     *
     * @testdox The action attributes can also be retrieved by the get() method.
     * @test
     */
    public function the_action_attributes_can_also_be_retrieved_by_the_get_method()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item',
            'quantity' => 1,
            'price'    => 1000
        ]);

        $id        = 1;
        $title     = 'Example action';
        $group     = 'discount';
        $value     = -100;
        $extraInfo = [
            'description' => 'Example extra information'
        ];

        $cartAction = $cart->applyAction([
            'id'         => $id,
            'title'      => $title,
            'group'      => $group,
            'value'      => $value,
            'extra_info' => $extraInfo
        ]);

        $itemAction = $item->applyAction([
            'id'         => $id,
            'title'      => $title,
            'group'      => $group,
            'value'      => $value,
            'extra_info' => $extraInfo
        ]);

        $this->assertInstanceOf(Action::class, $cartAction);
        $this->assertEquals($id, $cartAction->get('id'));
        $this->assertEquals($title, $cartAction->get('title'));
        $this->assertEquals($group, $cartAction->get('group'));
        $this->assertEquals($value, $cartAction->get('value'));
        $this->assertSimilarArray($extraInfo, $cartAction->get('extra_info'));

        $this->assertInstanceOf(Action::class, $itemAction);
        $this->assertEquals($id, $itemAction->get('id'));
        $this->assertEquals($title, $itemAction->get('title'));
        $this->assertEquals($group, $itemAction->get('group'));
        $this->assertEquals($value, $itemAction->get('value'));
        $this->assertSimilarArray($extraInfo, $itemAction->get('extra_info'));

        $this->assertNotEquals($cartAction, $itemAction);
    }

    /**
     * Can retrieve details of action as Laravel collection using the getDetails() method.
     *
     * @testdox Can retrieve details of action as Laravel collection using the getDetails() method.
     * @test
     */
    public function can_retrieve_details_of_action_as_laravel_collection_using_the_get_details_method()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item',
            'quantity' => 1,
            'price'    => 1000
        ]);

        $cartAction = $cart->applyAction(['id' => 1, 'title' => 'Example action']);
        $itemAction = $item->applyAction(['id' => 1, 'title' => 'Example action']);

        $this->assertInstanceOf(Collection::class, $cartAction->getDetails());
        $this->assertInstanceOf(Collection::class, $itemAction->getDetails());
    }

    /**
     * Can retrieve the cart instance that action belongs to using the getCart() method.
     *
     * @testdox Can retrieve the cart instance that action belongs to using the getCart() method.
     * @test
     */
    public function can_retrieve_the_cart_instance_that_action_belongs_to_using_the_getCart_method()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item',
            'quantity' => 1,
            'price'    => 1000
        ]);

        $cartAction       = $cart->applyAction(['id' => 1, 'title' => 'Example action']);
        $itemAction       = $item->applyAction(['id' => 1, 'title' => 'Example action']);
        $cartOfCartAction = $cartAction->getCart();
        $cartOfItemAction = $itemAction->getCart();

        $this->assertInstanceOf(Cart::class, $cartOfCartAction);
        $this->assertInstanceOf(Cart::class, $cartOfItemAction);
        $this->assertEquals($cart, $cartOfCartAction);
        $this->assertEquals($cart, $cartOfItemAction);
    }

    /**
     * Can retrieve the parent node instance that action belongs to using the getParentNode() method.
     *
     * @testdox Can retrieve the parent node instance that action belongs to using the getParentNode() method.
     * @test
     */
    public function can_retrieve_the_parent_node_instance_that_action_belongs_to_using_the_getParentNode_method()
    {
        $cart = $this->initCart();
        $item = $cart->addItem([
            'id'       => 1,
            'title'    => 'Example item',
            'quantity' => 1,
            'price'    => 1000
        ]);

        $cartAction = $cart->applyAction(['id' => 1, 'title' => 'Example action']);
        $itemAction = $item->applyAction(['id' => 1, 'title' => 'Example action']);

        $parentOfCartAction = $cartAction->getParentNode();
        $parentOfItemAction = $itemAction->getParentNode();

        $this->assertInstanceOf(Cart::class, $parentOfCartAction);
        $this->assertInstanceOf(Item::class, $parentOfItemAction);

        $this->assertEquals($cart, $parentOfCartAction);
        $this->assertEquals($item, $parentOfItemAction);
    }

    /**
     * Each action always has a hash code that can be retrieved by the getHash() method.
     *
     * @testdox Each action always has a hash code that can be retrieved by the getHash() method.
     * @test
     */
    public function each_action_always_has_a_hash_code_that_can_be_retrieved_by_the_getHash_method()
    {
        $cart   = $this->initCart();
        $action = $cart->applyAction(['id' => 1, 'title' => 'Example title']);

        $this->assertTrue(method_exists($action, 'getHash'));
        $this->assertEquals($action->getHash(), $action->get('hash'));
    }

    /**
     * Actions with different id and group attributes will have different hash codes.
     *
     * @testdox Actions with different id and group attributes will have different hash codes.
     * @test
     */
    public function actions_with_different_id_and_group_attributes_will_have_different_hash_codes()
    {
        $cart    = $this->initCart();
        $action1 = $cart->applyAction(['id' => 1, 'group' => 'group_1', 'title' => 'Example title 1']);
        $action2 = $cart->applyAction(['id' => 1, 'group' => 'group_2', 'title' => 'Example title 1']);
        $action3 = $cart->applyAction(['id' => 2, 'group' => 'group_1', 'title' => 'Example title 1']);

        $this->assertInstanceOf(Action::class, $action1);
        $this->assertInstanceOf(Action::class, $action2);
        $this->assertInstanceOf(Action::class, $action3);

        $this->assertNotEquals($action1, $action2);
        $this->assertNotEquals($action1->getHash(), $action2->getHash());

        $this->assertNotEquals($action1, $action3);
        $this->assertNotEquals($action1->getHash(), $action3->getHash());

        $this->assertNotEquals($action2, $action3);
        $this->assertNotEquals($action2->getHash(), $action3->getHash());
    }
}
