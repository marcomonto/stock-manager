<?php

namespace App\Http\Requests;

use App\Dtos\DeleteOrderDto;
use App\Enums\Dtos;
use App\Utils\ValidationPatterns;

class DeleteOrderRequest extends StockRequest
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
        ];
    }

    public function toDto(): DeleteOrderDto
    {
        /** @var DeleteOrderDto */
        return $this->dtoFactory->create(
            Dtos::DeleteOrder,
            $this->validated()
        );
    }
}
