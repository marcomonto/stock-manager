<?php

namespace Feature\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class OrderDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_successful_order_deletion(): void
    {
        $order = Order::query()->create([
            'id' => "01HV5L2K3O1N5A6Q7R8S9T0U2A",
            'name' => 'Ordine To Delete',
            'description' => 'Eliminami',
            'status' => OrderStatus::DELIVERED,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id
        ]);
        $response = $this->delete("api/orders/{$order->id}");
        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELLED->value
        ]);
    }

    public function test_delete_order_with_products_restores_stock(): void
    {
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 20]);

        $order = Order::factory()->create([
            'status' => OrderStatus::DELIVERED
        ]);

        $order->products()->attach([
            $product1->id => ['quantity' => 3],
            $product2->id => ['quantity' => 5]
        ]);

        $product1->update(['stock_quantity' => 7]);
        $product2->update(['stock_quantity' => 15]);

        $response = $this->delete("api/orders/{$order->id}");

        $response->assertStatus(200);

        $product1->refresh();
        $product2->refresh();

        $this->assertEquals(10, $product1->stock_quantity);
        $this->assertEquals(20, $product2->stock_quantity);
    }

    public function test_delete_already_cancelled_order(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::CANCELLED
        ]);

        $response = $this->delete("api/orders/{$order->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELLED->value
        ]);
    }

    public function test_delete_non_existing_order(): void
    {
        $nonExistingId = '01HXAMPLE1234567890ABCDEFG';

        $response = $this->delete("api/orders/{$nonExistingId}");

        $response->assertStatus(422); // InvalidArgumentException da OrderService::find()
    }

    public function test_delete_with_invalid_order_id_format(): void
    {
        $response = $this->delete('api/orders/invalid-id');

        $response->assertStatus(400);
    }

    public function test_delete_with_empty_order_id(): void
    {
        $response = $this->delete('api/orders/');

        $response->assertStatus(405);
    }

    public function test_delete_with_numeric_order_id(): void
    {
        $response = $this->delete('api/orders/123');

        $response->assertStatus(400);
    }

    public function test_delete_order_in_different_statuses(): void
    {
        $statuses = [
            OrderStatus::PENDING,
            OrderStatus::PROCESSING,
            OrderStatus::SHIPPED,
            OrderStatus::DELIVERED,
        ];

        foreach ($statuses as $status) {
            $order = Order::factory()->create(['status' => $status]);

            $response = $this->delete("api/orders/{$order->id}");

            $response->assertStatus(200);

            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => OrderStatus::CANCELLED->value
            ]);
        }
    }

    public function test_delete_order_multiple_times(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::DELIVERED
        ]);

        $response1 = $this->delete("api/orders/{$order->id}");
        $response1->assertStatus(200);

        $response2 = $this->delete("api/orders/{$order->id}");
        $response2->assertStatus(422);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELLED->value
        ]);
    }

    public function test_delete_order_with_complex_product_relationships(): void
    {
        $products = Product::factory()->count(5)->create([
            'stock_quantity' => 50
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::DELIVERED
        ]);

        $attachData = [];
        foreach ($products as $index => $product) {
            $quantity = ($index + 1) * 2; // 2, 4, 6, 8, 10
            $attachData[$product->id] = ['quantity' => $quantity];

            $product->update(['stock_quantity' => 50 - $quantity]);
        }

        $order->products()->attach($attachData);
        $response = $this->delete("api/orders/{$order->id}");

        $response->assertStatus(200);

        foreach ($products as $product) {
            $product->refresh();
            $this->assertEquals(50, $product->stock_quantity);
        }
    }

    public function test_delete_order_transaction_rollback_on_error(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::DELIVERED
        ]);
        $this->mock(\App\Services\OrderService::class, function ($mock) use ($order) {
            $mock->shouldReceive('delete')
                ->once()
                ->with($order->id)
                ->andThrow(new \Exception('Simulated error'));
        });

        $response = $this->delete("api/orders/{$order->id}");
        $response->assertStatus(500);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::DELIVERED->value
        ]);
    }

    public function test_delete_response_format(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::DELIVERED
        ]);

        $response = $this->delete("api/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertJson([]);
        $response->assertHeader('Content-Type', 'application/json');
    }
}
