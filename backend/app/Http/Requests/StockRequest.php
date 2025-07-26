<?php

namespace App\Http\Requests;

use App\Dtos\Dto;
use App\Enums\Dtos;
use App\Factories\DtoFactory;
use Illuminate\Foundation\Http\FormRequest;

abstract class StockRequest extends FormRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    public abstract function toDto(): Dto;

}
