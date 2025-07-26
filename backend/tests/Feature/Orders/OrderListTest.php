<?php

namespace Feature\Orders;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;

class OrderListTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_invalid_pagination_parameters(): void{
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

}
