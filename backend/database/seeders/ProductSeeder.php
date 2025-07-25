<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            Product::query()
                ->create([
                    'id' => Str::ulid(),
                    'name' => fake()->words(rand(2, 4), true),
                    'description' => fake()->paragraph(rand(2, 5)),
                    'stock_quantity' => fake()->numberBetween(0, 100),
                    'is_active' => fake()->boolean(80),
                ]);
        }
    }
}
