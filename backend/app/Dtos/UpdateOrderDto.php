<?php

namespace App\Dtos;

use App\Dtos\Dto;

readonly class UpdateOrderDto implements Dto
{
    public function __construct(
        public string $orderId,
        public array   $orderItems,
        public string $name,
        public string $description,
    )
    {}

    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'orderItems' => $this->orderItems,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
