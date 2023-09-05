<?php

namespace Tests\Feature;

use App\Models\Basket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BasketTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test getting all baskets
     */
    public function testGetAllBaskets(): void
    {
        $response = $this->get('/api/baskets');
        $response->assertJsonCount(10); // count of baskets inserted during seeding
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
     * Test getting all baskets
     */
    public function testAddingBasket(): void
    {
        $user = User::factory()->create();
        $user->save();
        $response = $this->post('/api/baskets',['user_id' => $user->id]);
        $response->assertStatus(200);
    }
}
