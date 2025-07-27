<?php

namespace App\Http\Requests;

use App\Dtos\CreateOrderDto;
use App\Utils\ValidationPatterns;

/**
 * @OA\Schema(
 *     schema="CreateOrderRequest",
 *     required={"orderItems", "name", "description"},
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
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Name for the order",
 *         example="Urgent delivery"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         maxLength=255,
 *         description="Description for the order",
 *         example="Order for Mr.Rossi in Catanzaro"
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
            'name' => ValidationPatterns::STRING_REQUIRED_255,
            'description' => ValidationPatterns::STRING_REQUIRED_255,
        ];
    }

    public function toDto(): CreateOrderDto
    {
        return new CreateOrderDto(
            orderItems: $this->validated('orderItems'),
            name: $this->validated('name'),
            description: $this->validated('description'),
        );
    }
}
