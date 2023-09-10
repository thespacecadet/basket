<?php

namespace Tests\Feature;

use App\Models\Basket;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class BasketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting all baskets
     */
    public function testGetAllBaskets(): void
    {
        $response = $this->get('/api/baskets');
        $this->assertCount(10,$response['content']); //amount of elements inserted during initial seeding
        $response->assertStatus(200);
    }

    /**
     * Test basket storing validation
     */
    public function testNewBasketValidation(): void
    {
        $response = $this->post('/api/baskets');
        $response->assertStatus(400);
    }

    /**
     * Test Adding basket
     */
    public function testAddingBasket(): void
    {
        $user = User::factory()->create();
        $user->save();
        $response = $this->post('/api/baskets',['user_id' => $user->id]);
        $response->assertStatus(200);

        //check that getting basket by user id is consistent
        $response = $this->get('/api/baskets/users/'.$user->id);
        $response->assertStatus(200);
    }

    /**
     * Test basket sum function
     */
    public function testBasketSumFunction(): void
    {
        //set 2 arbitrary quantities
        $quantity1 = rand(1,10);
        $quantity2 = rand(1,10);

        //create user and basket
        $user = User::factory()->create();
        $user->save();
        $basket = Basket::factory()->create(['user_id' => $user->id]);
        $basket->save();

        // create 2 products
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        $product1->save();
        $product2->save();

        $price1 = $product1->price;
        $price2 = $product2->price;

        //assign both products to the basket with the random quantities
        $basket->products()->attach($product1->id, ['quantity' => $quantity1]);
        $basket->products()->attach($product2->id, ['quantity' => $quantity2]);

        //expect the sum to be the product of both quantities and their prices
        $expectedSum = $price1 * $quantity1 + $price2 * $quantity2;
        $this->assertEquals($expectedSum,$basket->getBasketSum());
    }

    /**
     * Add and remove product through API
     */
    public function testAddingUpdatingAndRemovingProduct(): void
    {

        //create user, basket and product
        $user = User::factory()->create();
        $user->save();
        $response = $this->post('/api/baskets',['user_id' => $user->id]);
        $product = Product::factory()->create();
        $product->save();
        $basketData = $this->get('/api/baskets/users/'.$user->id);
        $basket = Basket::FindOrFail($basketData['content']['id']);

        $this->assertFalse($basket->hasProducts());

        //assign a product to our new basket
        $this->post('/api/baskets/'.$basket->id,['product_id' => $product->id]);
        $this->assertTrue($basket->hasProducts());
        $this->assertEquals($basket->products()->first()->id,$product->id);

        //check for default quantity
        $this->assertEquals($basket->products()->first()->pivot->quantity,1);

        //update quantity and check if it changed
        $fakeQuantity = 5; //arbitrary new quantity
        $this->patch('/api/baskets/'.$basket->id.'/products/'.$product->id,['quantity' => $fakeQuantity]);
        $this->assertEquals($basket->products()->first()->pivot->quantity,$fakeQuantity);

        //test deleting product
        $this->delete('/api/baskets/'.$basket->id,['product_id' => $product->id]);
        $this->assertFalse($basket->hasProducts());
    }
}
