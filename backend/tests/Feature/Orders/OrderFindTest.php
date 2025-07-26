<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class OrderFindTest extends TestCase
{
    use RefreshDatabase;

    protected array $basicResponseStructure = [
        'id',
        'name',
        'description',
        'status',
        'createdAt',
        'updatedAt',
    ];

    protected array $detailedResponseStructure = [
        'id',
        'name',
        'description',
        'status',
        'createdAt',
        'updatedAt',
        'orderItems',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_find_existing_order_without_details(): void
    {
        $order = Order::query()->first();
        $this->assertNotNull($order, 'No orders found in database');

        $response = $this->get("api/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure($this->basicResponseStructure);

        $responseData = $response->json();

        $this->assertEquals($order->id, $responseData['id']);
        $this->assertEquals($order->name, $responseData['name']);
        $this->assertEquals($order->description, $responseData['description']);
        $this->assertEquals($order->status->value, $responseData['status']);

        $this->assertIsString($responseData['id']);
        $this->assertMatchesRegularExpression('/^[0-9A-Z]{26}$/', $responseData['id']); // ULID format

        $this->assertIsString($responseData['name']);
        $this->assertNotEmpty($responseData['name']);

        $this->assertIsString($responseData['description']);
        $this->assertNotEmpty($responseData['description']);

        $this->assertIsString($responseData['status']);
        $this->assertContains($responseData['status'], ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled']);

        $this->assertIsString($responseData['createdAt']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $responseData['createdAt']);

        $this->assertIsString($responseData['updatedAt']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $responseData['updatedAt']);

        $this->assertArrayNotHasKey('orderItems', $responseData);
    }

    public function test_find_existing_order_with_details(): void
    {
        $order = Order::with('orderItems')->first();
        $this->assertNotNull($order, 'No orders found in database');

        $response = $this->get("api/orders/{$order->id}?withDetails=true");

        $response->assertStatus(200);
        $response->assertJsonStructure($this->detailedResponseStructure);
        $responseData = $response->json();

        $this->assertEquals($order->id, $responseData['id']);
        $this->assertEquals($order->name, $responseData['name']);
        $this->assertEquals($order->description, $responseData['description']);
        $this->assertEquals($order->status->value, $responseData['status']);

        $this->assertArrayHasKey('orderItems', $responseData);
        $this->assertIsArray($responseData['orderItems']);

        if (! empty($responseData['orderItems'])) {
            foreach ($responseData['orderItems'] as $orderItem) {
                $this->assertIsArray($orderItem);
                $this->assertArrayHasKey('name', $orderItem);
                $this->assertArrayHasKey('quantity', $orderItem);
                $this->assertArrayHasKey('createdAt', $orderItem);
                $this->assertArrayHasKey('updatedAt', $orderItem);

                $this->assertIsString($orderItem['name']);
                $this->assertIsInt($orderItem['quantity']);
                $this->assertGreaterThan(0, $orderItem['quantity']);
            }
        }
    }

    public function test_find_existing_order_with_details_false(): void
    {
        $order = Order::query()->first();
        $this->assertNotNull($order, 'No orders found in database');

        $response = $this->get("api/orders/{$order->id}?withDetails=false");

        $response->assertStatus(200);
        $response->assertJsonStructure($this->basicResponseStructure);

        $responseData = $response->json();

        $this->assertArrayNotHasKey('orderItems', $responseData);
    }

    public function test_find_existing_order_with_details_as_1(): void
    {
        $order = Order::query()->first();
        $this->assertNotNull($order, 'No orders found in database');

        $response = $this->get("api/orders/{$order->id}?withDetails=1");

        $response->assertStatus(200);
        $response->assertJsonStructure($this->detailedResponseStructure);

        $responseData = $response->json();

        // Should contain orderItems when withDetails=1
        $this->assertArrayHasKey('orderItems', $responseData);
        $this->assertIsArray($responseData['orderItems']);
    }

    public function test_find_non_existing_order(): void
    {
        $nonExistentId = '01HZ9999999999999999999999';

        $response = $this->get("api/orders/{$nonExistentId}");

        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Order not found',
        ]);
    }

    public function test_find_with_invalid_order_id_format(): void
    {
        $invalidIds = [
            'invalid-id',           // Not ULID format
            '123',                  // Too short
            'not-ulid-format',      // Invalid characters
            '',                     // Empty
            'TOOLONGTOBEVALIDULID123456789',
        ];

        foreach ($invalidIds as $invalidId) {
            $response = $this->get("api/orders/{$invalidId}");

            $response->assertStatus(400);
            $response->assertJsonStructure([
                'message',
                'error',
                'type',
            ]);

            $responseData = $response->json();
            $this->assertEquals('validation_error', $responseData['type']);
        }
    }

    public function test_find_with_invalid_with_details_parameter(): void
    {
        $order = Order::query()->first();
        $this->assertNotNull($order, 'No orders found in database');

        $invalidWithDetailsValues = [
            'invalid',
            '2',
            'yes',
            'no',
            'maybe',
        ];

        foreach ($invalidWithDetailsValues as $invalidValue) {
            $response = $this->get("api/orders/{$order->id}?withDetails={$invalidValue}");

            $response->assertStatus(400);
            $response->assertJsonStructure([
                'message',
                'error',
                'type',
            ]);

            $responseData = $response->json();
            $this->assertEquals('validation_error', $responseData['type']);
        }
    }

    public function test_find_with_valid_boolean_string_values(): void
    {
        $order = Order::query()->first();
        $this->assertNotNull($order, 'No orders found in database');

        $validTruthyValues = ['true', '1'];
        $validFalsyValues = ['false', '0'];

        foreach ($validTruthyValues as $truthyValue) {
            $response = $this->get("api/orders/{$order->id}?withDetails={$truthyValue}");

            $response->assertStatus(200);
            $responseData = $response->json();
            $this->assertArrayHasKey('orderItems', $responseData);
        }

        foreach ($validFalsyValues as $falsyValue) {
            $response = $this->get("api/orders/{$order->id}?withDetails={$falsyValue}");

            $response->assertStatus(200);
            $responseData = $response->json();
            $this->assertArrayNotHasKey('orderItems', $responseData);
        }
    }

    public function test_find_order_response_data_types(): void
    {
        $order = Order::query()->first();
        $this->assertNotNull($order, 'No orders found in database');

        $response = $this->get("api/orders/{$order->id}");
        $response->assertStatus(200);

        $responseData = $response->json();

        $this->assertIsString($responseData['id']);
        $this->assertIsString($responseData['name']);
        $this->assertIsString($responseData['description']);
        $this->assertIsString($responseData['status']);
        $this->assertIsString($responseData['createdAt']);
        $this->assertIsString($responseData['updatedAt']);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $responseData['createdAt']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $responseData['updatedAt']);

        $this->assertMatchesRegularExpression('/^[0-9A-Z]{26}$/', $responseData['id']);

        $validStatuses = array_map(fn ($status) => $status->value, OrderStatus::cases());
        $this->assertContains($responseData['status'], $validStatuses);
    }

    public function test_find_order_with_empty_query_parameter(): void
    {
        $order = Order::query()->first();
        $this->assertNotNull($order, 'No orders found in database');

        $response = $this->get("api/orders/{$order->id}?withDetails=");

        $response->assertStatus(200);
        $responseData = $response->json();

        $this->assertArrayNotHasKey('orderItems', $responseData);
    }

    public function test_response_structure_consistency(): void
    {
        $order = Order::query()->first();
        $this->assertNotNull($order, 'No orders found in database');

        $basicResponse = $this->get("api/orders/{$order->id}");
        $basicResponse->assertStatus(200);

        $detailedResponse = $this->get("api/orders/{$order->id}?withDetails=true");
        $detailedResponse->assertStatus(200);

        $basicData = $basicResponse->json();
        $detailedData = $detailedResponse->json();

        foreach (['id', 'name', 'description', 'status', 'createdAt', 'updatedAt'] as $field) {
            $this->assertEquals($basicData[$field], $detailedData[$field]);
        }

        $this->assertArrayNotHasKey('orderItems', $basicData);
        $this->assertArrayHasKey('orderItems', $detailedData);
    }
}
