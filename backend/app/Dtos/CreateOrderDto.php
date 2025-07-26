<?php

namespace App\Dtos;

readonly class CreateOrderDto implements Dto
{
    public function __construct(
        public array  $orderItems,
        public string $name,
        public string $description,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'orderItems' => $this->orderItems,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
