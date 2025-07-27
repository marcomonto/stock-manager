<?php

namespace Feature\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class OrderUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_successful_order_update(): void
    {
        $originalProduct1 = Product::factory()->create(['stock_quantity' => 100]);
        $originalProduct2 = Product::factory()->create(['stock_quantity' => 50]);

        $order = Order::factory()->create([
            'name' => 'Original Order',
            'description' => 'Original description',
            'status' => OrderStatus::DELIVERED,
        ]);

        $order->products()->attach([
            $originalProduct1->id => ['quantity' => 5],
            $originalProduct2->id => ['quantity' => 3],
        ]);

        $originalProduct1->update(['stock_quantity' => 95]); // 100 - 5
        $originalProduct2->update(['stock_quantity' => 47]); // 50 - 3

        $newProduct1 = Product::factory()->create(['stock_quantity' => 80]);
        $newProduct2 = Product::factory()->create(['stock_quantity' => 30]);

        $updateData = [
            'name' => 'Updated Order Name',
            'description' => 'Updated order description',
            'orderItems' => [
                [$newProduct1->id, 10],
                [$newProduct2->id, 5],
            ],
        ];

        $response = $this->putJson("api/orders/{$order->id}", $updateData);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('Updated Order Name', $order->name);
        $this->assertEquals('Updated order description', $order->description);

        $originalProduct1->refresh();
        $originalProduct2->refresh();
        $this->assertEquals(100, $originalProduct1->stock_quantity); // Stock ripristinato
        $this->assertEquals(50, $originalProduct2->stock_quantity);   // Stock ripristinato

        $newProduct1->refresh();
        $newProduct2->refresh();
        $this->assertEquals(70, $newProduct1->stock_quantity); // 80 - 10
        $this->assertEquals(25, $newProduct2->stock_quantity); // 30 - 5

        $this->assertCount(2, $order->orderItems);
        $productIds = $order->orderItems->pluck('product_id');
        $this->assertTrue($productIds->contains($newProduct1->id));
        $this->assertTrue($productIds->contains($newProduct2->id));
    }

    public function test_update_order_with_same_products_different_quantities(): void
    {
        $product1 = Product::factory()->create(['stock_quantity' => 100]);
        $product2 = Product::factory()->create(['stock_quantity' => 50]);

        $order = Order::factory()->create([
            'status' => OrderStatus::DELIVERED,
        ]);

        $order->products()->attach([
            $product1->id => ['quantity' => 10],
            $product2->id => ['quantity' => 5],
        ]);

        $product1->update(['stock_quantity' => 90]); // 100 - 10
        $product2->update(['stock_quantity' => 45]); // 50 - 5

        $updateData = [
            'name' => 'Updated Order',
            'description' => 'Updated description',
            'orderItems' => [
                [$product1->id, 15], // Era 10, ora 15
                [$product2->id, 8],   // Era 5, ora 8
            ],
        ];

        $response = $this->putJson("api/orders/{$order->id}", $updateData);

        $response->assertStatus(200);

        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(85, $product1->stock_quantity); // 100 - 15
        $this->assertEquals(42, $product2->stock_quantity); // 50 - 8
    }

    public function test_update_cancelled_order_fails(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $order = Order::factory()->create([
            'status' => OrderStatus::CANCELLED,
        ]);

        $updateData = [
            'name' => 'Updated Order',
            'description' => 'This should fail',
            'orderItems' => [
                [$product->id, 5],
            ],
        ];

        $response = $this->putJson("api/orders/{$order->id}", $updateData);

        $response->assertStatus(422);

        $order->refresh();
        $this->assertNotEquals('Updated Order', $order->name);

        $product->refresh();
        $this->assertEquals(50, $product->stock_quantity);
    }

    public function test_update_order_insufficient_stock(): void
    {
        $originalProduct = Product::factory()->create(['stock_quantity' => 50]);
        $newProduct = Product::factory()->create(['stock_quantity' => 5]);

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        $order->products()->attach([
            $originalProduct->id => ['quantity' => 10],
        ]);

        $originalProduct->update(['stock_quantity' => 40]); // 50 - 10

        $updateData = [
            'name' => 'Updated Order',
            'description' => 'This should fail',
            'orderItems' => [
                [$newProduct->id, 10],
            ],
        ];

        $response = $this->putJson("api/orders/{$order->id}", $updateData);

        $response->assertStatus(422);

        // Verifica rollback
        $originalProduct->refresh();
        $newProduct->refresh();
        $this->assertEquals(40, $originalProduct->stock_quantity);
        $this->assertEquals(5, $newProduct->stock_quantity);
    }

    public function test_update_order_with_non_existing_product(): void
    {
        $originalProduct = Product::factory()->create(['stock_quantity' => 50]);
        $fakeProductId = strtoupper(\Illuminate\Support\Str::ulid());

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        $order->products()->attach([
            $originalProduct->id => ['quantity' => 10],
        ]);

        $originalProduct->update(['stock_quantity' => 40]);

        $updateData = [
            'name' => 'Updated Order',
            'description' => 'This should fail',
            'orderItems' => [
                [$fakeProductId, 5],
            ],
        ];

        $response = $this->putJson("api/orders/{$order->id}", $updateData);

        $response->assertStatus(422);

        $originalProduct->refresh();
        $this->assertEquals(40, $originalProduct->stock_quantity);
    }

    public function test_update_non_existing_order(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);
        $fakeOrderId = strtoupper(\Illuminate\Support\Str::ulid());

        $updateData = [
            'name' => 'Updated Order',
            'description' => 'This should fail',
            'orderItems' => [
                [$product->id, 5],
            ],
        ];

        $response = $this->putJson("api/orders/{$fakeOrderId}", $updateData);

        $response->assertStatus(422);

        $product->refresh();
        $this->assertEquals(50, $product->stock_quantity);
    }

    public function test_update_order_validation_missing_required_fields(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::PENDING]);
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $response = $this->putJson("api/orders/{$order->id}", [
            'description' => 'Valid description',
            'orderItems' => [[$product->id, 1]],
        ]);
        $response->assertStatus(400);

        $response = $this->putJson("api/orders/{$order->id}", [
            'name' => 'Valid name',
            'orderItems' => [[$product->id, 1]],
        ]);
        $response->assertStatus(400);

        $response = $this->putJson("api/orders/{$order->id}", [
            'name' => 'Valid name',
            'description' => 'Valid description',
        ]);
        $response->assertStatus(400);
    }

    public function test_update_order_validation_invalid_order_items(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::PENDING]);
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $response = $this->putJson("api/orders/{$order->id}", [
            'name' => 'Valid name',
            'description' => 'Valid description',
            'orderItems' => [['invalid-ulid', 1]],
        ]);
        $response->assertStatus(400);

        $response = $this->putJson("api/orders/{$order->id}", [
            'name' => 'Valid name',
            'description' => 'Valid description',
            'orderItems' => [[$product->id, -1]],
        ]);
        $response->assertStatus(400);

        $response = $this->putJson("api/orders/{$order->id}", [
            'name' => 'Valid name',
            'description' => 'Valid description',
            'orderItems' => [[$product->id, 0]],
        ]);
        $response->assertStatus(400);
    }

    public function test_update_order_validation_invalid_order_id(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $updateData = [
            'name' => 'Valid name',
            'description' => 'Valid description',
            'orderItems' => [[$product->id, 1]],
        ];

        $response = $this->putJson('api/orders/invalid-ulid', $updateData);
        $response->assertStatus(400);

        $response = $this->putJson('api/orders/123', $updateData);
        $response->assertStatus(400);
    }

    public function test_update_order_with_duplicate_products_fails(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        $updateData = [
            'name' => 'Updated Order',
            'description' => 'This should fail due to duplicate product IDs',
            'orderItems' => [
                [$product->id, 2],
                [$product->id, 3],
            ],
        ];

        $response = $this->putJson("api/orders/{$order->id}", $updateData);

        $response->assertStatus(422);
    }

    public function test_update_order_transaction_rollback_on_error(): void
    {
        $originalProduct = Product::factory()->create(['stock_quantity' => 50]);
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 5]);

        $order = Order::factory()->create([
            'name' => 'Original Order',
            'description' => 'Original description',
            'status' => OrderStatus::PENDING,
        ]);

        $order->products()->attach([
            $originalProduct->id => ['quantity' => 10],
        ]);

        $originalProduct->update(['stock_quantity' => 40]);

        $updateData = [
            'name' => 'Transaction Test Order',
            'description' => 'This should rollback',
            'orderItems' => [
                [$product1->id, 5],
                [$product2->id, 10],
            ],
        ];

        $response = $this->putJson("api/orders/{$order->id}", $updateData);

        $response->assertStatus(422);

        // Verifica Rollback
        $order->refresh();
        $this->assertEquals('Original Order', $order->name);
        $this->assertEquals('Original description', $order->description);

        $originalProduct->refresh();
        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(40, $originalProduct->stock_quantity); // Come prima
        $this->assertEquals(10, $product1->stock_quantity);        // Non cambiato
        $this->assertEquals(5, $product2->stock_quantity);         // Non cambiato

        $productIds = $order->orderItems->pluck('product_id');
        $this->assertTrue($productIds->contains($originalProduct->id));
        $this->assertFalse($productIds->contains($product1->id));
        $this->assertFalse($productIds->contains($product2->id));
    }

    public function test_update_order_response_format(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $order = Order::factory()->create([
            'status' => OrderStatus::PENDING,
        ]);

        $updateData = [
            'name' => 'Response Format Test',
            'description' => 'Testing response format',
            'orderItems' => [[$product->id, 5]],
        ];

        $response = $this->putJson("api/orders/{$order->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson([]);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_update_order_with_different_status_values(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $modifiableStatuses = [
            OrderStatus::PENDING,
            OrderStatus::PROCESSING,
            OrderStatus::SHIPPED,
            OrderStatus::DELIVERED,
        ];

        foreach ($modifiableStatuses as $status) {
            $order = Order::factory()->create(['status' => $status]);

            $updateData = [
                'name' => "Updated Order - {$status->value}",
                'description' => 'This should work',
                'orderItems' => [[$product->id, 1]],
            ];

            $response = $this->putJson("api/orders/{$order->id}", $updateData);

            $response->assertStatus(200);

            $order->refresh();
            $this->assertEquals("Updated Order - {$status->value}", $order->name);
        }
    }

    public function test_update_order_removes_all_previous_items(): void
    {
        $originalProduct1 = Product::factory()->create(['stock_quantity' => 50]);
        $originalProduct2 = Product::factory()->create(['stock_quantity' => 30]);
        $newProduct = Product::factory()->create(['stock_quantity' => 40]);

        $order = Order::factory()->create(['status' => OrderStatus::PENDING]);

        $order->products()->attach([
            $originalProduct1->id => ['quantity' => 5],
            $originalProduct2->id => ['quantity' => 3],
        ]);

        $originalProduct1->update(['stock_quantity' => 45]);
        $originalProduct2->update(['stock_quantity' => 27]);

        $updateData = [
            'name' => 'Single Product Order',
            'description' => 'Now only one product',
            'orderItems' => [[$newProduct->id, 10]],
        ];

        $response = $this->putJson("api/orders/{$order->id}", $updateData);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertCount(1, $order->orderItems);
        $this->assertEquals($newProduct->id, $order->orderItems->first()->product_id);

        $originalProduct1->refresh();
        $originalProduct2->refresh();
        $this->assertEquals(50, $originalProduct1->stock_quantity);
        $this->assertEquals(30, $originalProduct2->stock_quantity);

        $newProduct->refresh();
        $this->assertEquals(30, $newProduct->stock_quantity);
    }
}
