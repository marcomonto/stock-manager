<?php

namespace App\Http\Requests;

use App\Dtos\Dto;
use App\Dtos\ListOrderDto;
use App\Utils\ValidationPatterns;

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

    public function toDto(): Dto
    {
        return new ListOrderDto(
          withDetails: !empty($this->validated('withDetails')),
          page: $this->validated('page'),
          rowsPerPage: $this->validated('rowsPerPage'),
          name: $this->validated('name'),
          description: $this->validated('description'),
          creationDate: $this->validated('creationDate'),
        );
    }
}
