<?php

namespace App\Http\Requests;

use App\Dtos\UpdateOrderDto;
use App\Enums\Dtos;
use App\Utils\ValidationPatterns;

class UpdateOrderRequest extends StockRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'orderId' => ValidationPatterns::ULID_REQUIRED,
            'orderItems' => ValidationPatterns::ARRAY_REQUIRED,
            'orderItems.*' => ValidationPatterns::ARRAY_REQUIRED,
            'orderItems.*.0' => ValidationPatterns::ULID_REQUIRED,
            'orderItems.*.1' => ValidationPatterns::INT_REQUIRED_POSITIVE,
            'notes' => ValidationPatterns::STRING_NULLABLE,
        ];
    }

    public function toDto(): UpdateOrderDto
    {
        /** @var UpdateOrderDto */
        return $this->dtoFactory
            ->create(
                Dtos::UpdateOrder,
                $this->validated()
            );
    }
}
