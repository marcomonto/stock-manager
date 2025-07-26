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
            'name' => ValidationPatterns::STRING_REQUIRED_255,
            'description' => ValidationPatterns::STRING_REQUIRED_255,
        ];
    }

    public function toDto(): UpdateOrderDto
    {
        return new UpdateOrderDto(
            orderId: $this->validated('orderId'),
            orderItems: $this->validated('orderItems'),
            name: $this->validated('name'),
            description: $this->validated('description'),
        );
    }
}
