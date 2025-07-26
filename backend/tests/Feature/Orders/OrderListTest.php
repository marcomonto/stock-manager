<?php

namespace Feature\Orders;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class OrderListTest extends TestCase
{
    use RefreshDatabase;

    protected array $responseStructure = [
        'id',
        'name',
        'description',
        'status',
        'createdAt',
        'updatedAt',
    ];

    protected array $responseStructureWithDetails = [
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

    public function test_invalid_pagination_parameters(): void
    {
        $response = $this->get('api/orders');
        $response->assertStatus(400);

        $response = $this->get('api/orders?page=ciao');
        $response->assertStatus(400);

        $response = $this->get('api/orders?page=1');
        $response->assertStatus(400);

        $response = $this->get('api/orders?page=1&rowsPerPage=0');
        $response->assertStatus(400);

        $response = $this->get('api/orders?page=0&rowsPerPage=10');
        $response->assertStatus(400);

        $response = $this->get('api/orders?page=1&rowsPerPage=string');
        $response->assertStatus(400);
    }

    public function test_invalid_query_parameters(): void
    {
        $baseQuery = 'api/orders?page=1&rowsPerPage=10';

        $response = $this->get("$baseQuery&withDetails=10");
        $response->assertStatus(400);

        $response = $this->get("$baseQuery&creationDate=10");
        $response->assertStatus(400);

        $longString = str_repeat('a', 300);

        $response = $this->get("$baseQuery&name=$longString");
        $response->assertStatus(400);

        $response = $this->get("$baseQuery&description=$longString");
        $response->assertStatus(400);
    }

    public function test_simple_paginated_list(): void
    {
        $baseQuery = 'api/orders';

        $response = $this->get("$baseQuery?page=1&rowsPerPage=10");
        $response->assertStatus(200);
        $response->assertJsonCount(10);

        $response->assertJsonStructure([
            '*' => $this->responseStructure
        ]);

        $responseFirstFive = $this->get("$baseQuery?page=1&rowsPerPage=5");
        $responseFirstFive->assertStatus(200);
        $responseFirstFive->assertJsonCount(5);

        $responseSecondFive = $this->get("$baseQuery?page=2&rowsPerPage=5");
        $responseSecondFive->assertStatus(200);
        $responseSecondFive->assertJsonCount(5);

        $orders = $responseSecondFive->json();
        foreach ($orders as $order) {
            $this->assertArrayHasKeys($this->responseStructure, $order);

            $this->assertIsString($order['id']);
            $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $order['id']); // ULID format

            $this->assertIsString($order['name']);
            $this->assertNotEmpty($order['name']);

            $this->assertIsString($order['description']);
            $this->assertNotEmpty($order['description']);

            $this->assertIsString($order['status']);
            $this->assertContains($order['status'], ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled']);

            $this->assertIsString($order['createdAt']);
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $order['createdAt']);

            $this->assertIsString($order['updatedAt']);
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $order['updatedAt']);
        }
        $firstPageIds = collect($responseFirstFive->json())->pluck('id')->toArray();
        $secondPageIds = collect($responseSecondFive->json())->pluck('id')->toArray();
        $this->assertEmpty(array_intersect($firstPageIds, $secondPageIds));
    }

    public function test_list_with_details(): void
    {
        $baseQuery = 'api/orders?page=1&rowsPerPage=5&withDetails=1';

        $response = $this->get($baseQuery);
        $response->assertStatus(200);
        $response->assertJsonCount(5);

        $response->assertJsonStructure([
            '*' => $this->responseStructureWithDetails
        ]);

        $orders = $response->json();
        foreach ($orders as $order) {
            $this->assertArrayHasKeys($this->responseStructureWithDetails, $order);

            $this->assertIsArray($order['orderItems']);

            if (!empty($order['orderItems'])) {
                foreach ($order['orderItems'] as $orderItem) {
                    $this->assertArrayHasKeys(['name', 'quantity', 'createdAt', 'updatedAt'], $orderItem);
                    $this->assertIsString($orderItem['name']);
                    $this->assertIsInt($orderItem['quantity']);
                    $this->assertGreaterThan(0, $orderItem['quantity']);
                }
            }
        }
    }

    public function test_list_with_filters(): void
    {
        $baseQuery = 'api/orders?page=1&rowsPerPage=10';

        $response = $this->get("$baseQuery&name=Gaming");
        $response->assertStatus(200);

        $orders = $response->json();
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $this->assertStringContainsString('Gaming', $order['name']);
            }
        }

        $response = $this->get("$baseQuery&status=completed");
        $response->assertStatus(200);

        $orders = $response->json();
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $this->assertEquals(OrderStatus::DELIVERED->value, $order['status']);
            }
        }

        $response = $this->get("$baseQuery&description=gaming");
        $response->assertStatus(200);

        $orders = $response->json();
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $this->assertStringContainsString('gaming', strtolower($order['description']));
            }
        }
    }

    public function test_empty_result_pagination(): void
    {
        $response = $this->get('api/orders?page=999&rowsPerPage=10');
        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    public function test_valid_date_filter(): void
    {
        $today = now()->format('Y-m-d');
        $response = $this->get("api/orders?page=1&rowsPerPage=10&creationDate=$today");
        $response->assertStatus(200);

        $orders = $response->json();
        foreach ($orders as $order) {
            $orderDate = date('Y-m-d', strtotime($order['createdAt']));
            $this->assertEquals($today, $orderDate);
        }
    }

    private function assertArrayHasKeys(array $expectedKeys, array $array): void
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array, "Missing key: $key");
        }
    }
}
