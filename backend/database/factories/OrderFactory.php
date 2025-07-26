<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'id' => strtoupper(Str::ulid()),
            'name' => 'Ordine '.fake()->words(2, true),
            'description' => fake()->sentence(8),
            'status' => fake()->randomElement(OrderStatus::cases()),
        ];
    }
}
