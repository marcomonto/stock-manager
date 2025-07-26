<?php

namespace App\Http\Requests;

use App\Dtos\CreateOrderDto;
use App\Dtos\Dto;
use App\Enums\Dtos;
use App\Factories\DtoFactory;
use App\Utils\ValidationPatterns;

/**
 * @OA\Schema(
 *     schema="CreateOrderRequest",
 *     required={"orderItems"},
 *     @OA\Property(
 *         property="orderItems",
 *         type="array",
 *         description="Array of order items, each containing product ID and quantity",
 *         @OA\Items(
 *             type="array",
 *             minItems=2,
 *             maxItems=2,
 *             @OA\Items(
 *                 oneOf={
 *                     @OA\Schema(type="string", format="ulid", description="Product ULID"),
 *                     @OA\Schema(type="integer", minimum=1, description="Quantity")
 *                 }
 *             )
 *         ),
 *         example={
 *             {"01HV5R2K3M4N5P6Q7R8S9T0U1V", 2},
 *             {"01HW6S3L4N5O6P7Q8R9S0T1U2W", 1}
 *         }
 *     ),
 *     @OA\Property(
 *         property="notes",
 *         type="string",
 *         nullable=true,
 *         description="Optional notes for the order",
 *         example="Consegna urgente richiesta"
 *     )
 * )
 */
class CreateOrderRequest extends StockRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'orderItems' => ValidationPatterns::ARRAY_REQUIRED,
            'orderItems.*' => ValidationPatterns::ARRAY_REQUIRED,
            'orderItems.*.0' => ValidationPatterns::ULID_REQUIRED,
            'orderItems.*.1' => ValidationPatterns::INT_REQUIRED_POSITIVE,
            'notes' => ValidationPatterns::STRING_NULLABLE,
        ];
    }

    public function toDto(): CreateOrderDto
    {
        return new CreateOrderDto(
            orderItems: $this->validated('orderItems'),
            notes: $this->validated('notes'),
        );
    }
}
