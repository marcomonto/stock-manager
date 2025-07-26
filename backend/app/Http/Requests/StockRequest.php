<?php

namespace App\Http\Requests;

use App\Dtos\Dto;
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

    abstract public function toDto(): Dto;
}
