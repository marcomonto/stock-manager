<?php

namespace App\Http\Requests;

use App\Dtos\DeleteOrderDto;
use App\Utils\ValidationPatterns;

/**
 * @OA\Parameter(
 *     parameter="orderId",
 *     name="orderId",
 *     in="path",
 *     required=true,
 *     description="Order ULID identifier",
 *
 *     @OA\Schema(
 *         type="string",
 *         format="ulid",
 *         pattern="^[0-9A-HJKMNP-TV-Z]{26}$",
 *         example="01HV5R2K3M4N5P6Q7R8S9T0U1V"
 *     )
 * )
 */
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

    public function prepareForValidation()
    {
        $this->merge([
            'orderId' => $this->route('orderId'),
        ]);
    }

    public function toDto(): DeleteOrderDto
    {
        return new DeleteOrderDto(
            orderId: $this->validated('orderId'),
        );
    }
}
