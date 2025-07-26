<?php

namespace App\Dtos;

readonly class FindOrderDto implements Dto
{
    public function __construct(
        public string $orderId,
        public bool $withDetails,
    ) {}

    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'withDetails' => $this->withDetails,
        ];
    }
}
