<?php

namespace Tests\Feature;

use App\Models\Basket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BasketTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');
        $basket = Basket::all();
        echo $basket;
        $response->assertStatus(200);
    }
}
