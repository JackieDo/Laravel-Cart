<?php

use Illuminate\Support\Collection;
use Jackiedo\Cart\Cart;
use Jackiedo\Cart\Tax;
use Orchestra\Testbench\TestCase;

require_once __DIR__ . '/Traits/CommonSetUp.php';

class TaxTest extends TestCase
{
    use CommonSetUp;

    /**
     * The tax attributes can be retrieved using a corresponding getter.
     *
     * @testdox The tax attributes can be retrieved using a corresponding getter.
     * @test
     */
    public function the_tax_attributes_can_be_retrieved_using_a_corresponding_getter()
    {
        $cart      = $this->initCart();
        $id        = 1;
        $title     = 'Example tax';
        $rate      = 10;
        $extraInfo = [
            'description' => 'Example extra information'
        ];
        $tax = $cart->applyTax([
            'id'         => $id,
            'title'      => $title,
            'rate'       => $rate,
            'extra_info' => $extraInfo
        ]);

        $this->assertInstanceOf(Tax::class, $tax);
        $this->assertEquals($id, $tax->getId());
        $this->assertEquals($title, $tax->getTitle());
        $this->assertEquals($rate, $tax->getRate());
        $this->assertSimilarArray($extraInfo, $tax->getExtraInfo());
    }

    /**
     * The tax attributes can also be retrieved by the get() method.
     *
     * @testdox The tax attributes can also be retrieved by the get() method.
     * @test
     */
    public function the_tax_attributes_can_also_be_retrieved_by_the_get_method()
    {
        $cart      = $this->initCart();
        $id        = 1;
        $title     = 'Example tax';
        $rate      = 10;
        $extraInfo = [
            'description' => 'Example extra information'
        ];
        $tax = $cart->applyTax([
            'id'         => $id,
            'title'      => $title,
            'rate'       => $rate,
            'extra_info' => $extraInfo
        ]);

        $this->assertInstanceOf(Tax::class, $tax);
        $this->assertEquals($id, $tax->get('id'));
        $this->assertEquals($title, $tax->get('title'));
        $this->assertEquals($rate, $tax->get('rate'));
        $this->assertSimilarArray($extraInfo, $tax->get('extra_info'));
    }

    /**
     * Can retrieve details of tax as Laravel collection using the getDetails() method.
     *
     * @testdox Can retrieve details of tax as Laravel collection using the getDetails() method.
     * @test
     */
    public function can_retrieve_details_of_tax_as_laravel_collection_using_the_get_details_method()
    {
        $cart = $this->initCart();
        $tax  = $cart->applyTax(['id' => 1, 'title' => 'Example title']);

        $this->assertInstanceOf(Collection::class, $tax->getDetails());
    }

    /**
     * Can retrieve the cart instance that tax belongs to using the getCart() method.
     *
     * @testdox Can retrieve the cart instance that tax belongs to using the getCart() method.
     * @test
     */
    public function can_retrieve_the_cart_instance_that_tax_belongs_to_using_the_getCart_method()
    {
        $cart      = $this->initCart();
        $tax       = $cart->applyTax(['id' => 1, 'title' => 'Example title']);
        $cartOfTax = $tax->getCart();

        $this->assertInstanceOf(Cart::class, $cartOfTax);
        $this->assertEquals($cart, $cartOfTax);
    }

    /**
     * Can also retrieve the cart instance that tax belongs to using the getParentNode() method.
     *
     * @testdox Can also retrieve the cart instance that tax belongs to using the getParentNode() method.
     * @test
     */
    public function can_also_retrieve_the_cart_instance_that_tax_belongs_to_using_the_getParentNode_method()
    {
        $cart      = $this->initCart();
        $tax       = $cart->applyTax(['id' => 1, 'title' => 'Example title']);
        $cartOfTax = $tax->getParentNode();

        $this->assertInstanceOf(Cart::class, $cartOfTax);
        $this->assertEquals($cart, $cartOfTax);
    }

    /**
     * Each tax always has a hash code that can be retrieved by the getHash() method.
     *
     * @testdox Each tax always has a hash code that can be retrieved by the getHash() method.
     * @test
     */
    public function each_tax_always_has_a_hash_code_that_can_be_retrieved_by_the_getHash_method()
    {
        $cart = $this->initCart();
        $tax  = $cart->applyTax(['id' => 1, 'title' => 'Example title']);

        $this->assertTrue(method_exists($tax, 'getHash'));
        $this->assertEquals($tax->getHash(), $tax->get('hash'));
    }

    /**
     * Taxes with different id attribute will have different hash codes.
     *
     * @testdox Taxes with different id attribute will have different hash codes.
     * @test
     */
    public function taxes_with_different_id_attribute_will_have_different_hash_codes()
    {
        $cart = $this->initCart();
        $tax1 = $cart->applyTax(['id' => 1, 'title' => 'Example title 1']);
        $tax2 = $cart->applyTax(['id' => 2, 'title' => 'Example title 2']);

        $this->assertInstanceOf(Tax::class, $tax1);
        $this->assertInstanceOf(Tax::class, $tax2);

        $this->assertNotEquals($tax1, $tax2);
        $this->assertNotEquals($tax1->getHash(), $tax2->getHash());
    }

    /**
     * The detailed of tax always has the hash, id, title, rate, amount and extra_info attributes.
     *
     * @testdox The detailed of tax always has the hash, id, title, rate, amount and extra_info attributes.
     * @test
     */
    public function the_detailed_of_tax_always_has_the_hash_id_title_rate_amount_and_extra_info_attributes()
    {
        $cart       = $this->initCart();
        $tax        = $cart->applyTax(['id' => 1, 'title' => 'Example title 1']);
        $taxDetails = $tax->getDetails();

        $this->assertTrue($taxDetails->has(['hash', 'id', 'title', 'rate', 'amount', 'extra_info']));
    }

    /**
     * Can update attributes except id using the update() method.
     *
     * @testdox Can update attributes except id using the update() method.
     * @test
     */
    public function can_update_attributes_except_id_using_the_update_method()
    {
        $cart = $this->initCart();
        $tax  = $cart->applyTax([
            'id'         => 1,
            'title'      => 'Demo tax',
            'rate'       => 10,
            'extra_info' => ['description' => 'Demo extra information']
        ]);

        $this->assertEquals(1, $tax->getId());
        $this->assertEquals('Demo tax', $tax->getTitle());
        $this->assertEquals(10, $tax->getRate());
        $this->assertSimilarArray(['description' => 'Demo extra information'], $tax->getExtraInfo());

        $updated = $tax->update([
            'id'         => 2,
            'title'      => 'Updated title',
            'rate'       => 20,
            'extra_info' => ['description' => 'Updated extra information']
        ]);

        $this->assertNotEquals(2, $updated->getId());
        $this->assertEquals(1, $updated->getId());
        $this->assertEquals('Updated title', $updated->getTitle());
        $this->assertEquals(20, $updated->getRate());
        $this->assertSimilarArray(['description' => 'Updated extra information'], $updated->getExtraInfo());
        $this->assertEquals($tax, $updated);
    }
}
