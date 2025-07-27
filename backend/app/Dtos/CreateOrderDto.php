<?php

namespace App\Dtos;

readonly class CreateOrderDto implements Dto
{
    /**
     * @param  array<int, array{string, int}>  $orderItems  Array di tuple [productId, quantity]
     */
    public function __construct(
        public array $orderItems,
        public string $name,
        public string $description,
    ) {}

    /**
     * @return array{orderItems: array<int, array{string, int}>, name: string, description: string}
     */
    public function toArray(): array
    {
        return [
            'orderItems' => $this->orderItems,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
