<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Basket;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         \App\Models\User::factory(10)->create()->each(function ($user) {
             \App\Models\Basket::factory(1)->create(['user_id'=>$user->id]);
         });

         $this->call([
             ProductSeeder::class
         ]);
    }
}
