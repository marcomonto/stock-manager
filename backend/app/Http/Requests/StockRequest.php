<?php

namespace App\Http\Requests;

use App\Dtos\Dto;
use App\Enums\Dtos;
use App\Factories\DtoFactory;
use Illuminate\Foundation\Http\FormRequest;

abstract class StockRequest extends FormRequest
{
    protected Dtos $associatedDto;

    public function __construct(
        protected DtoFactory $dtoFactory,
    )
    {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function toDto(array $data): Dto
    {
        return $this->dtoFactory->create(
            $this->associatedDto,
            $data
        );
    }


}
