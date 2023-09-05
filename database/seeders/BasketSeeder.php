<?php

namespace Database\Seeders;

use App\Models\Basket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BasketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Basket::factory()
            ->count(10)
            ->create();
    }
}
