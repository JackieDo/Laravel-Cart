<?php

use Jackiedo\Cart\Cart;
use Jackiedo\Cart\Contracts\UseCartable;
use Jackiedo\Cart\Traits\CanUseCart;
use Orchestra\Testbench\TestCase;

class CartTest extends TestCase
{

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Jackiedo\Cart\CartServiceProvider'];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return ['Cart' => 'Jackiedo\Cart\Facades\Cart'];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('session.driver', 'array');
    }

    /**
     * We always have a default cart instance
     *
     * @test
     */
    public function we_always_have_a_default_cart_instance()
    {
        $cart = $this->getCart();

        $this->assertEquals(Cart::DEFAULT_INSTANCE, $cart->getInstance());
    }

    /**
     * Test multibility nature of the cart
     *
     * @test
     */
    public function we_can_have_multiple_instances()
    {
        $cart = $this->getCart();

        $cart1 = $cart->instance('wishlist')->getInstance();
        $cart2 = $cart->instance('shopping')->getInstance();

        $this->assertNotEquals($cart1, $cart2);
    }

    /**
     * Test multibility nature of the cart
     *
     * @test
     */
    public function different_carts_must_be_different()
    {
        $cart = $this->getCart();

        $cartItem1 = $cart->instance('wishlist')->add(1, 'Item name');
        $cartItem2 = $cart->instance('shopping')->add(1, 'Item name');

        $this->assertEquals(1, $cart->instance('wishlist')->countQuantities());
        $this->assertEquals(1, $cart->instance('shopping')->countQuantities());
    }

    /**
     * Test structure of the cart item
     *
     * @test
     */
    public function the_added_item_must_be_the_wellformed_cartitem()
    {
        $cart = $this->getCart();

        $cartItem = $cart->add(1, 'Item name');

        $this->validateCartItemStructure($cartItem);

        $this->validateHash($this->genHash(1), $cartItem);
    }

    /**
     * Test the validation of identifier argument when adding item to the cart
     *
     * @test
     */
    public function it_will_throw_exception_with_the_invalid_identifier_of_item()
    {
        $this->setExpectedException('Jackiedo\Cart\Exceptions\CartInvalidArgumentException');

        $cart = $cart = $this->getCart();

        $cart->add(null, 'Example title', 1, 10.00);
    }

    /**
     * Test the validation of title argument when adding item to the cart
     *
     * @test
     */
    public function it_will_throw_exception_with_the_invalid_title_of_item()
    {
        $this->setExpectedException('Jackiedo\Cart\Exceptions\CartInvalidArgumentException');

        $cart = $cart = $this->getCart();

        $cart->add(1, null, 1, 10.00);
    }

    /**
     * Test the validation of quantity argument when adding item to the cart
     *
     * @test
     */
    public function it_will_throw_exception_with_the_invalid_quantity_of_item()
    {
        $this->setExpectedException('Jackiedo\Cart\Exceptions\CartInvalidArgumentException');

        $cart = $cart = $this->getCart();

        $cart->add(1, 'Example title', 'Invalid quantity', 10.00);
    }

    /**
     * Test the validation of price argument when adding item to the cart
     *
     * @test
     */
    public function it_will_throw_exception_with_the_invalid_price_of_item()
    {
        $this->setExpectedException('Jackiedo\Cart\Exceptions\CartInvalidArgumentException');

        $cart = $cart = $this->getCart();

        $cart->add(1, 'Example title', 1, 'Invalid price');
    }

    /**
     * Test the update() method of the cart
     *
     * @test
     */
    public function we_can_add_an_item_with_full_attributes()
    {
        $cart = $this->getCart();

        $id = 1;
        $title = 'Item name';
        $qty = 1;
        $price = 10.00;
        $options = [
            'size' => 'M',
            'color' => 'red'
        ];

        $cartItem = $cart->add($id, $title, $qty, $price, $options);

        $this->validateCartItemStructure($cartItem);

        $this->validateHash($this->genHash($id, null, $options), $cartItem);

        $this->assertEquals(1, $cart->countQuantities());
    }

    /**
     * Test the add() method of the cart
     *
     * @test
     */
    public function the_cartitem_will_be_updated_if_already_exists_in_the_cart()
    {
        $cart = $this->getCart();

        $id = 1;
        $title = 'Item name';
        $qty = 1;
        $price = 10.00;
        $options = [
            'size' => 'M',
            'color' => 'red'
        ];

        $cart->add($id, $title, $qty, $price, $options);
        $cart->add($id, $title, $qty, $price, $options);

        $this->assertEquals(2, $cart->countQuantities());
        $this->assertEquals(1, $cart->countItems());
    }

    /**
     * Test the update() method of the cart
     *
     * @test
     */
    public function we_can_update_the_quantity_of_an_existing_item_in_the_cart()
    {
        $cart = $this->getCart();

        $id = 1;
        $title = 'Item name';
        $qty = 1;
        $price = 10.00;
        $options = [
            'size' => 'M',
            'color' => 'red'
        ];

        $cartItem = $cart->add($id, $title, $qty, $price, $options);
        $firstHash = $cartItem->hash;

        $cartItem = $cart->update($firstHash, 2);
        $secondHash = $cartItem->hash;

        $this->assertEquals(2, $cart->countQuantities());
        $this->assertEquals(1, $cart->countItems());
        $this->assertEquals($firstHash, $secondHash);
    }

    /**
     * Test the update() method of the cart
     *
     * @test
     */
    public function we_can_update_the_attributes_of_an_existing_item_in_the_cart()
    {
        $cart = $this->getCart();

        $cartItem = $cart->add(1, 'Item name', 1, 10, ['size' => 'M', 'color' => 'red']);
        $firstHash = $cartItem->hash;

        $cartItem = $cart->update($firstHash, ['title' => 'New item name', 'qty' => 5, 'options' => ['size' => 'L', 'color' => 'yellow']]);

        $this->assertEquals(5, $cart->countQuantities());
        $this->assertEquals(1, $cart->countItems());
        $this->assertEquals('New item name', $cartItem->title);
        $this->assertEquals('L', $cartItem->options->size);
        $this->assertEquals('yellow', $cartItem->options->color);
    }

    /**
     * Check for strict binding when updating the cart item's informations
     *
     * @test
     */
    public function we_cannot_update_the_identifier_of_an_existing_item_in_the_cart()
    {
        $cart = $this->getCart();

        $cartItem = $cart->add(1, 'Item name', 1, 10, ['size' => 'M', 'color' => 'red']);
        $firstHash = $cartItem->hash;

        $cartItem = $cart->update($firstHash, ['id' => 100]);

        $this->assertEquals(1, $cartItem->id);
    }

    /**
     * Check for strict binding when updating the cart item's informations
     *
     * @test
     */
    public function we_cannot_update_the_subtotal_of_an_existing_item_in_the_cart()
    {
        $cart = $this->getCart();

        $cartItem = $cart->add(1, 'Item name', 1, 10, ['size' => 'M', 'color' => 'red']);
        $firstHash = $cartItem->hash;

        $cartItem = $cart->update($firstHash, ['subtotal' => 1000]);

        $this->assertEquals(10, $cartItem->subtotal);
    }

    /**
     * Check for strict binding when updating the cart item's informations
     *
     * @test
     */
    public function we_cannot_update_the_associated_of_an_existing_item_in_the_cart()
    {
        $cart = $this->getCart();

        $cartItem = $cart->add(1, 'Item name', 1, 10, ['size' => 'M', 'color' => 'red']);
        $firstHash = $cartItem->hash;

        $cartItem = $cart->update($firstHash, ['associated' => 'Product']);

        $this->assertEquals(null, $cartItem->associated);
    }

    /**
     * Test for generating hash of the cart item
     *
     * @test
     */
    public function these_cart_item_must_be_are_two_different_cart_item()
    {
        $cart = $this->getCart();

        $cartItem1 = $cart->add(1, 'Item name', 1, 10, ['size' => 'M', 'color' => 'red']);

        $cartItem2 = $cart->add(1, 'Item name', 1, 10, ['size' => 'L', 'color' => 'yellow']);

        $this->assertEquals(2, $cart->countQuantities());
        $this->assertEquals(2, $cart->countItems());
        $this->assertNotEquals($cartItem1->hash, $cartItem2->hash);
    }

    /**
     * Test the seach() method of the cart with the second parameter is true
     *
     * @test
     */
    public function this_searching_must_return_one_cartitem()
    {
        $cart = $this->getCart();

        $cartItem1 = $cart->add(1, 'Item name', 1, 10, ['size' => 'M', 'color' => 'red']);

        $cartItem2 = $cart->add(1, 'Item name', 1, 10, ['size' => 'M', 'color' => 'yellow']);

        $search = $cart->search([
            'title'   => 'Item name',
            'options' => [
                'color' => 'red'
            ]
        ], true);

        $this->assertEquals(1, $search->count());
    }

    /**
     * Test the seach() method of the cart with the second parameter is false
     *
     * @test
     */
    public function this_searching_must_return_two_cartitem()
    {
        $cart = $this->getCart();

        $cartItem1 = $cart->add(1, 'Item name', 1, 10, ['size' => 'M', 'color' => 'red']);

        $cartItem2 = $cart->add(1, 'Item name', 1, 10, ['size' => 'M', 'color' => 'yellow']);

        $search = $cart->search([
            'title' => 'Item name',
            'options' => [
                'color' => 'red'
            ]
        ], false);

        $this->assertEquals([
            $cartItem1->hash => [
                'hash'       => $cartItem1->hash,
                'id'         => 1,
                'title'      => 'Item name',
                'qty'        => 1,
                'price'      => 10.00,
                'subtotal'   => 10.00,
                'options'    => [
                    'size'  => 'M',
                    'color' => 'red'
                ],
                'associated' => null
            ],
            $cartItem2->hash => [
                'hash'       => $cartItem2->hash,
                'id'         => 1,
                'title'      => 'Item name',
                'qty'        => 1,
                'price'      => 10.00,
                'subtotal'   => 10.00,
                'options'    => [
                    'size'  => 'M',
                    'color' => 'yellow'
                ],
                'associated' => null
            ]
        ], $cart->all()->toArray());
    }

    /**
     * Test associatation the cart item with a model
     *
     * @test
     */
    public function we_can_associate_cartitem_with_a_model()
    {
        $product = new ProductModel;

        $cart = $this->getCart();

        $cartItem = $cart->add($product);

        $this->assertEquals(get_class($product), $cartItem->associated);
        $this->assertEquals($product->id, $cartItem->id);
        $this->assertEquals($product->name, $cartItem->title);
        $this->assertEquals($product->unit_price, $cartItem->price);
        $this->assertEquals($this->genHash(1, get_class($product)), $cartItem->hash);
        $this->assertEquals($product->name, $cartItem->model->name);
        $this->assertEquals($product->unit_price, $cartItem->model->unit_price);
    }

    /**
     * Test the hasInCart() method of an associated model
     *
     * @test
     */
    public function recently_added_model_has_in_the_cart()
    {
        $product = new ProductModel;

        $cart = $this->getCart();

        $cartItem = $cart->add($product);

        $this->assertTrue($product->hasInCart());
    }

    /**
     * Test the allFromCart() method for an associated model
     *
     * @test
     */
    public function there_must_be_two_entities_of_the_product_in_the_cart()
    {
        $product = new ProductModel;

        $cart = $this->getCart();

        $cartItem1 = $cart->add($product);
        $cartItem2 = $cart->add($product, 1, ['size' => 'L']);

        $allProductInCart = $product->allFromCart();

        $this->assertEquals([
            $cartItem1->hash => [
                'hash'       => $cartItem1->hash,
                'id'         => $product->id,
                'title'      => $product->name,
                'qty'        => 1,
                'price'      => $product->unit_price,
                'subtotal'   => 1 * $product->unit_price,
                'options'    => [],
                'associated' => get_class($product)

            ],
            $cartItem2->hash => [
                'hash'       => $cartItem2->hash,
                'id'         => $product->id,
                'title'      => $product->name,
                'qty'        => 1,
                'price'      => $product->unit_price,
                'subtotal'   => 1 * $product->unit_price,
                'options'    => [
                    'size' => 'L'
                ],
                'associated' => get_class($product)
            ]
        ], $cart->all()->toArray());
    }

    /**
     * Get an instance of the cart.
     *
     * @return \Jackiedo\Cart\Cart
     */
    private function getCart()
    {
        $session = $this->app->make('session');
        $events = $this->app->make('events');

        $cart = new Cart($session, $events);

        return $cart;
    }

    /**
     * Generate hash for cart item
     */
    private function genHash($id, $associated = null, $options = [])
    {
        ksort($options);
        return md5($id . serialize($associated) . serialize($options));
    }

    /**
     * Validate structure the of cart item
     *
     * @param  \Jackiedo\Cart\CartItem $cartItem
     */
    private function validateCartItemStructure($cartItem)
    {
        $this->assertInstanceOf('Jackiedo\Cart\CartItem', $cartItem);

        $this->assertTrue($cartItem->has('hash'));
        $this->assertTrue($cartItem->has('id'));
        $this->assertTrue($cartItem->has('title'));
        $this->assertTrue($cartItem->has('qty'));
        $this->assertTrue($cartItem->has('price'));
        $this->assertTrue($cartItem->has('subtotal'));
        $this->assertTrue($cartItem->has('options'));
        $this->assertTrue($cartItem->has('associated'));
    }

    /**
     * Validate Hash
     *
     * @param  string $hash
     * @param  \Jackiedo\Cart\CartItem $cartItem
     */
    private function validateHash($hash, $cartItem)
    {
        $this->assertEquals($hash, $cartItem->hash);
    }
}

/**
 * This is a sample model use to associated with the cart item
 */
class ProductModel implements UseCartable
{
    use CanUseCart;

    /**
     * The identifier of model
     *
     * @var integer
     */
    public $id = 1;

    /**
     * The name of model
     *
     * @var string
     */
    public $name = 'Polo T-shirt for men';

    /**
     * The price of model
     *
     * @var float
     */
    public $unit_price = 10.00;

    /**
     * Get the identifier of the UseCartable item.
     *
     * @return int|string
     */
    public function getUseCartableId()
    {
        return $this->id;
    }

    /**
     * Get the title of the UseCartable item.
     *
     * @return string
     */
    public function getUseCartableTitle()
    {
        return $this->name;
    }

    /**
     * Get the price of the UseCartable item.
     *
     * @return float
     */
    public function getUseCartablePrice()
    {
        return $this->unit_price;
    }

    /**
     * Find a model by its identifier
     *
     * @param  int  $id  The identifier of model
     *
     * @return \Illuminate\Support\Collection|static|null
     */
    public function findById($id)
    {
        return $this;
    }
}
