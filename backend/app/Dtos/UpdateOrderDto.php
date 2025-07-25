<?php

namespace App\Dtos;

use App\Dtos\Dto;

readonly class UpdateOrderDto implements Dto
{
    public function __construct(
        public string $orderId,
        public array   $orderItems,
        public ?string $notes = null,
    )
    {}

    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'orderItems' => $this->orderItems,
            'notes' => $this->notes,
        ];
    }
}
