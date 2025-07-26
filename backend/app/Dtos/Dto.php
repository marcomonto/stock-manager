<?php

namespace App\Dtos;

interface Dto
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
