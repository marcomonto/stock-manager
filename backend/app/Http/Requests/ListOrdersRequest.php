<?php

namespace App\Http\Requests;

use App\Dtos\ListOrderDto;
use App\Utils\ValidationPatterns;


/**
 * @OA\Parameter(
 *     parameter="ListOrdersPage",
 *     name="page",
 *     in="query",
 *     required=true,
 *     description="Page number for pagination",
 *     @OA\Schema(type="integer", minimum=1, example=1)
 * )
 *
 * @OA\Parameter(
 *     parameter="ListOrdersRowsPerPage",
 *     name="rowsPerPage",
 *     in="query",
 *     required=true,
 *     description="Number of items per page",
 *     @OA\Schema(type="integer", minimum=1, maximum=100, example=10)
 * )
 *
 * @OA\Parameter(
 *     parameter="ListOrdersName",
 *     name="name",
 *     in="query",
 *     required=false,
 *     description="Filter orders by name (partial match)",
 *     @OA\Schema(type="string", maxLength=255, example="Urgent delivery")
 * )
 *
 * @OA\Parameter(
 *     parameter="ListOrdersDescription",
 *     name="description",
 *     in="query",
 *     required=false,
 *     description="Filter orders by description (partial match)",
 *     @OA\Schema(type="string", maxLength=255, example="Mr.Rossi")
 * )
 *
 * @OA\Parameter(
 *     parameter="ListOrdersCreationDate",
 *     name="creationDate",
 *     in="query",
 *     required=false,
 *     description="Filter orders by creation date",
 *     @OA\Schema(type="string", format="date", example="2024-01-15")
 * )
 *
 * @OA\Parameter(
 *     parameter="ListOrdersWithDetails",
 *     name="withDetails",
 *     in="query",
 *     required=false,
 *     description="Include detailed order information",
 *     @OA\Schema(type="boolean", example=true)
 * )
 */
class ListOrdersRequest extends StockRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => ValidationPatterns::INT_REQUIRED_POSITIVE,
            'rowsPerPage' => ValidationPatterns::INT_REQUIRED_POSITIVE,
            'name' => ValidationPatterns::STRING_NULLABLE_255,
            'description' => ValidationPatterns::STRING_NULLABLE_255,
            'creationDate' => ValidationPatterns::DATE_NULLABLE,
            'withDetails' => ValidationPatterns::BOOL_NULLABLE,
        ];
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function toDto(): ListOrderDto
    {
        return new ListOrderDto(
            withDetails: !empty($this->validated('withDetails')),
            page: $this->validated('page'),
            rowsPerPage: $this->validated('rowsPerPage'),
            name: $this->validated('name'),
            description: $this->validated('description'),
            creationDate: !empty($this->validated('creationDate')) ?
                new \DateTime($this->validated('creationDate')) :
                null,
        );
    }
}
