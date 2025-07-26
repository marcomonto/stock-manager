<?php

namespace App\Dtos;

readonly class DeleteOrderDto implements Dto
{
    public function __construct(
        public string $orderId,
    ) {}

    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
        ];
    }
}
