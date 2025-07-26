<?php

namespace App\Http\Requests;

use App\Dtos\FindOrderDto;
use App\Utils\ValidationPatterns;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Parameter(
 *     parameter="FindOrderWithDetails",
 *     name="withDetails",
 *     in="query",
 *     required=false,
 *     description="Include detailed order information with related items",
 *     @OA\Schema(
 *         type="boolean",
 *         example=true,
 *         enum={true, false}
 *     )
 * )
 */
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
        Log::info($this->route('orderId'));
        $this->merge([
           'orderId' => $this->route('orderId'),
           'withDetails' => $this->query('withDetails'),
        ]);
    }

    public function toDto(): FindOrderDto
    {
        return new FindOrderDto(
            orderId: $this->validated('orderId'),
            withDetails: ValidationPatterns::toBoolean($this->validated('withDetails')),

        );
    }
}
