<?php

namespace App\Factories;

use App\Dtos\Dto;
use App\Enums\Dtos;

class DtoFactory
{
    private string $dtoNamespace = 'App\\Dtos\\';

    public function create(Dtos $dtoName, array $data = []): Dto
    {
        $className = $this->dtoNamespace . $dtoName->value;
        return new $className($data);

    }
}
