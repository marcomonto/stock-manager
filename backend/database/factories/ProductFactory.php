<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'id' => strtoupper(Str::ulid()),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(10),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'is_active' => fake()->boolean(85),
        ];
    }
}
