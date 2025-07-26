<?php

namespace App\Http\Requests;

use App\Dtos\Dto;
use App\Dtos\FindOrderDto;
use App\Enums\Dtos;
use App\Utils\ValidationPatterns;

class FindOrderRequest extends StockRequest
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
            'withDetails' => ValidationPatterns::BOOL_NULLABLE,
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
           'orderId' => $this->query('orderId'),
           'withDetails' => $this->query('withDetails'),
        ]);
    }

    public function toDto(): FindOrderDto
    {
        return new FindOrderDto(
            orderId: $this->validated('orderId'),
            withDetails: $this->validated('withDetails'),
        );
    }
}
