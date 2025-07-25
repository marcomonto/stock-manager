<?php

namespace App\Dtos;

readonly class CreateOrderDto implements Dto
{
    public function __construct(
        public array   $orderItems,
        public ?string $notes = null,
    )
    {}

    public function toArray(): array
    {
        return [
            'orderItems' => $this->orderItems,
            'notes' => $this->notes,
        ];
    }
}
