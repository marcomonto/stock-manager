<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Gateways\CacheGateway;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Utils\PaginationOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;

class OrderCacheTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected CacheGateway $cacheGateway;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->orderService = app(OrderService::class);
        $this->cacheGateway = app(CacheGateway::class);
    }

    public function test_order_find_caches_result(): void
    {
        $order = Order::factory()->create([
            'name' => 'Cache Test Order',
            'description' => 'Order for cache testing',
            'status' => OrderStatus::DELIVERED,
        ]);

        Log::shouldReceive('info')
            ->with('Cache miss', ['cache_key' => "orders:{$order->id}"])
            ->once();

        $firstResult = $this->orderService->find($order->id);
        $this->assertEquals($order->id, $firstResult->id);
        $this->assertEquals($order->name, $firstResult->name);

        $secondResult = $this->orderService->find($order->id);
        $this->assertEquals($order->id, $secondResult->id);
        $this->assertEquals($order->name, $secondResult->name);

        $this->assertTrue(Cache::has("orders:{$order->id}"));
    }

    public function test_order_get_caches_result(): void
    {
        $order = Order::factory()->create([
            'name' => 'Get Cache Test',
            'status' => OrderStatus::PENDING,
        ]);

        Log::shouldReceive('info')
            ->with('Cache miss', ['cache_key' => "orders:{$order->id}"])
            ->once();

        $firstResult = $this->orderService->get($order->id);
        $this->assertNotNull($firstResult);
        $this->assertEquals($order->id, $firstResult->id);

        $secondResult = $this->orderService->get($order->id);
        $this->assertNotNull($secondResult);
        $this->assertEquals($order->id, $secondResult->id);

        $this->assertEquals($firstResult->toArray(), $secondResult->toArray());
    }

    public function test_order_list_caches_results(): void
    {
        Order::factory()->count(5)->create([
            'name' => 'List Cache Test',
            'status' => OrderStatus::DELIVERED,
        ]);

        $expectedCacheKey = 'orders:list::name:null:desc:null:date:null:page:1:per_page:5';
        Log::shouldReceive('info')
            ->with('Cache miss', ['cache_key' => $expectedCacheKey])
            ->once();

        $firstResult = $this->orderService->list(
            paginationOptions: new PaginationOptions(
                page: 1,
                rowsPerPage: 5
            )
        );
        $this->assertCount(5, $firstResult);

        $secondResult = $this->orderService->list(
            paginationOptions: new PaginationOptions(
                page: 1,
                rowsPerPage: 5
            )
        );
        $this->assertCount(5, $secondResult);

        $this->assertEquals($firstResult->toArray(), $secondResult->toArray());
        $this->assertTrue(Cache::has($expectedCacheKey));
    }

    public function test_order_list_with_filters_caches_separately(): void
    {
        Order::factory()->create(['name' => 'Gaming Setup', 'status' => OrderStatus::DELIVERED]);
        Order::factory()->create(['name' => 'Office Setup', 'status' => OrderStatus::DELIVERED]);


        $result1 = $this->orderService->list(name: 'Gaming');
        $result2 = $this->orderService->list(name: 'Office');
        $result3 = $this->orderService->list();

        $this->assertCount(1, $result1);
        $this->assertCount(1, $result2);
        $this->assertCount(2, $result3);

        // Verify different cache keys exist
        $key1 = 'orders:list::name:Gaming:desc:null:date:null:page:null:per_page:null';
        $key2 = 'orders:list::name:Office:desc:null:date:null:page:null:per_page:null';
        $key3 = 'orders:list::name:null:desc:null:date:null:page:null:per_page:null';

        $this->assertTrue(Cache::has($key1));
        $this->assertTrue(Cache::has($key2));
        $this->assertTrue(Cache::has($key3));
    }

    public function test_cache_invalidation_on_order_creation(): void
    {
        Order::factory()->count(3)->create(['status' => OrderStatus::DELIVERED]);
        $initialList = $this->orderService->list();
        $this->assertCount(3, $initialList);

        $listCacheKey = 'orders:list::name:null:desc:null:date:null:page:null:per_page:null';
        $this->assertTrue(Cache::has($listCacheKey));

        $product = Product::factory()->create(['stock_quantity' => 10]);
        $this->orderService->create(
            orderItems: [[$product->id, 2]],
            name: 'New Order',
            description: 'Cache invalidation test'
        );

        $this->assertFalse(Cache::has($listCacheKey));
        $updatedList = $this->orderService->list();
        $this->assertCount(4, $updatedList);
    }

    public function test_cache_invalidation_on_order_update(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 20]);

        $order = $this->orderService->create(
            orderItems: [[$product->id, 5]],
            name: 'Original Name',
            description: 'Original description'
        );

        $cachedOrder = $this->orderService->find($order->id);
        $this->assertEquals('Original Name', $cachedOrder->name);

        $orderCacheKey = "orders:{$order->id}";
        $this->assertTrue(Cache::has($orderCacheKey));

        $newProduct = Product::factory()->create(['stock_quantity' => 15]);
        $this->orderService->update(
            order: $order,
            orderItems: [[$newProduct->id, 3]],
            name: 'Updated Name',
            description: 'Updated description'
        );

        $this->assertFalse(Cache::has($orderCacheKey));

        $updatedOrder = $this->orderService->find($order->id);
        $this->assertEquals('Updated Name', $updatedOrder->name);
        $this->assertEquals('Updated description', $updatedOrder->description);
    }

    public function test_cache_invalidation_on_order_deletion(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $order = $this->orderService->create(
            orderItems: [[$product->id, 2]],
            name: 'To Delete',
            description: 'Will be deleted'
        );

        $this->orderService->find($order->id);
        $this->orderService->list();

        $orderCacheKey = "orders:{$order->id}";
        $listCacheKey = 'orders:list::name:null:desc:null:date:null:page:null:per_page:null';

        $this->assertTrue(Cache::has($orderCacheKey));
        $this->assertTrue(Cache::has($listCacheKey));

        $this->orderService->delete($order->id);

        $this->assertFalse(Cache::has($orderCacheKey));
        $this->assertFalse(Cache::has($listCacheKey));
    }

    public function test_cache_with_api_endpoints(): void
    {
        $order = Order::factory()->create([
            'name' => 'API Cache Test',
            'status' => OrderStatus::DELIVERED,
        ]);

        $response1 = $this->get("api/orders/{$order->id}");
        $response1->assertStatus(200);

        $orderCacheKey = "orders:{$order->id}";
        $this->assertTrue(Cache::has($orderCacheKey));

        $response2 = $this->get("api/orders/{$order->id}");
        $response2->assertStatus(200);

        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_cache_prefix_isolation(): void
    {
        $order = Order::factory()->create(['name' => 'Prefix Test']);

        $this->orderService->find($order->id);

        $orderCacheKey = "orders:{$order->id}";
        $this->assertTrue(Cache::has($orderCacheKey));

        $this->assertFalse(Cache::has($order->id));
        $this->assertFalse(Cache::has("products:{$order->id}"));
    }

}
