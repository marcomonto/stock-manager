<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();
        $products = Product::all();

        foreach ($orders as $order) {
            $numberOfItems = rand(1, 5);
            $selectedProducts = $products->random($numberOfItems);

            foreach ($selectedProducts as $product) {
                OrderItem::query()->create([
                    'id' => Str::ulid(),
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                ]);
            }
        }
    }
}
