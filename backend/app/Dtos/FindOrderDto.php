<?php

namespace App\Dtos;

use App\Dtos\Dto;

readonly class FindOrderDto implements Dto
{

    public function __construct(
        public string $orderId,
        public bool $withDetails,
    )
    {

    }

    public function toArray(): array
    {
        return [
            'orderId' => $this->orderId,
            'withDetails' => $this->withDetails,
        ];
    }
}
