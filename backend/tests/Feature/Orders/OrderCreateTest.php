<?php

namespace Feature\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class OrderCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_successful_order_creation(): void
    {
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 20]);

        $orderData = [
            'name' => 'Test Order',
            'description' => 'Test order description',
            'orderItems' => [
                [$product1->id, 3],
                [$product2->id, 5]
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'name' => 'Test Order',
            'description' => 'Test order description',
            'status' => OrderStatus::DELIVERED->value
        ]);

        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(7, $product1->stock_quantity); // 10 - 3
        $this->assertEquals(15, $product2->stock_quantity); // 20 - 5

        $order = Order::query()->where('name', 'Test Order')->first();
        $this->assertCount(2, $order->orderItems);
        $productIds = $order->orderItems->pluck('product_id');
        $this->assertTrue($productIds->contains($product1->id));
        $this->assertTrue($productIds->contains($product2->id));
    }

    public function test_create_order_with_single_product(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $orderData = [
            'name' => 'Single Product Order',
            'description' => 'Order with only one product',
            'orderItems' => [
                [$product->id, 10]
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'name' => 'Single Product Order',
            'description' => 'Order with only one product'
        ]);

        $product->refresh();
        $this->assertEquals(40, $product->stock_quantity);
        $order = Order::query()->where('name', 'Single Product Order')->first();

        $productIds = $order->orderItems->pluck('product_id');
        $this->assertTrue($productIds->contains($product->id));
    }

    public function test_create_order_with_multiple_same_product(): void
    {
        $product1 = Product::factory()->create(['stock_quantity' => 100]);
        $product2 = Product::factory()->create(['stock_quantity' => 50]);

        $orderData = [
            'name' => 'Multiple Products Order',
            'description' => 'Order with multiple different products',
            'orderItems' => [
                [$product1->id, 5],
                [$product2->id, 3],
                [$product1->id, 2],
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('orders', [
            'name' => 'Multiple Products Order'
        ]);
        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(100, $product1->stock_quantity);
        $this->assertEquals(50, $product2->stock_quantity);
        $this->assertDatabaseMissing('order_items', [
            'product_id' => $product1->id
        ]);
        $this->assertDatabaseMissing('order_items', [
            'product_id' => $product2->id
        ]);
    }

    public function test_create_order_insufficient_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $orderData = [
            'name' => 'Insufficient Stock Order',
            'description' => 'This should fail',
            'orderItems' => [
                [$product->id, 10]
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('orders', [
            'name' => 'Insufficient Stock Order'
        ]);

        $product->refresh();
        $this->assertEquals(5, $product->stock_quantity);
    }

    public function test_create_order_with_non_existing_product(): void
    {
        $fakeProductId = strtoupper(\Illuminate\Support\Str::ulid());

        $orderData = [
            'name' => 'Non-existing Product Order',
            'description' => 'This should fail',
            'orderItems' => [
                [$fakeProductId, 1]
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('orders', [
            'name' => 'Non-existing Product Order'
        ]);
    }

    public function test_create_order_validation_missing_required_fields(): void
    {
        $response = $this->postJson('api/orders', [
            'description' => 'Test description',
            'orderItems' => [
                [Product::factory()->create()->id, 1]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('api/orders', [
            'name' => 'Test name',
            'orderItems' => [
                [Product::factory()->create()->id, 1]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('api/orders', [
            'name' => 'Test name',
            'description' => 'Test description'
        ]);
        $response->assertStatus(400);
    }

    public function test_create_order_validation_invalid_order_items_format(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->postJson('api/orders', [
            'name' => 'Test Order',
            'description' => 'Test description',
            'orderItems' => 'invalid'
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('api/orders', [
            'name' => 'Test Order',
            'description' => 'Test description',
            'orderItems' => []
        ]);
        $response->assertStatus(400);

        // Test con formato item invalido (solo un elemento)
        $response = $this->postJson('api/orders', [
            'name' => 'Test Order',
            'description' => 'Test description',
            'orderItems' => [
                [$product->id]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('api/orders', [
            'name' => 'Test Order',
            'description' => 'Test description',
            'orderItems' => [
                ['invalid-ulid', 1]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('api/orders', [
            'name' => 'Test Order',
            'description' => 'Test description',
            'orderItems' => [
                [$product->id, -1]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('api/orders', [
            'name' => 'Test Order',
            'description' => 'Test description',
            'orderItems' => [
                [$product->id, 0]
            ]
        ]);
        $response->assertStatus(400);
    }

    public function test_create_order_validation_string_length(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $longString = str_repeat('a', 300);

        $response = $this->postJson('api/orders', [
            'name' => $longString,
            'description' => 'Valid description',
            'orderItems' => [
                [$product->id, 1]
            ]
        ]);
        $response->assertStatus(400);

        $response = $this->postJson('api/orders', [
            'name' => 'Valid name',
            'description' => $longString,
            'orderItems' => [
                [$product->id, 1]
            ]
        ]);
        $response->assertStatus(400);
    }

    public function test_create_order_transaction_rollback_on_error(): void
    {
        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 5]);

        $orderData = [
            'name' => 'Transaction Test Order',
            'description' => 'This should rollback',
            'orderItems' => [
                [$product1->id, 3],
                [$product2->id, 10]
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('orders', [
            'name' => 'Transaction Test Order'
        ]);

        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(10, $product1->stock_quantity);
        $this->assertEquals(5, $product2->stock_quantity);
    }

    public function test_create_order_with_inactive_product(): void
    {
        $activeProduct = Product::factory()->create([
            'stock_quantity' => 10,
            'is_active' => true
        ]);

        $inactiveProduct = Product::factory()->create([
            'stock_quantity' => 10,
            'is_active' => false
        ]);

        $orderData = [
            'name' => 'Mixed Products Order',
            'description' => 'Order with active and inactive products',
            'orderItems' => [
                [$activeProduct->id, 2],
                [$inactiveProduct->id, 1]
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(422);
    }

    public function test_create_order_response_format(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $orderData = [
            'name' => 'Response Format Test',
            'description' => 'Testing response format',
            'orderItems' => [
                [$product->id, 1]
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(201);
        $response->assertJson([]);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_create_order_with_large_quantity(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 1000]);

        $orderData = [
            'name' => 'Large Quantity Order',
            'description' => 'Order with large quantity',
            'orderItems' => [
                [$product->id, 999]
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(201);

        $product->refresh();
        $this->assertEquals(1, $product->stock_quantity);
    }

    public function test_create_order_edge_case_exact_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $orderData = [
            'name' => 'Exact Stock Order',
            'description' => 'Order that uses exact available stock',
            'orderItems' => [
                [$product->id, 5]
            ]
        ];

        $response = $this->postJson('api/orders', $orderData);

        $response->assertStatus(201);

        $product->refresh();
        $this->assertEquals(0, $product->stock_quantity);
    }
}
