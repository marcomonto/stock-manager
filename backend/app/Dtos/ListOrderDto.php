<?php

namespace App\Dtos;

readonly class ListOrderDto implements Dto
{
    public function __construct(
        public bool $withDetails,
        public ?int $page = null,
        public ?int $rowsPerPage = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?\DateTime $creationDate = null,
    ){}

    public function toArray(): array
    {
        return [
            'withDetails' => $this->withDetails,
            'page' => $this->page,
            'rowsPerPage' => $this->rowsPerPage,
            'name' => $this->name,
            'description' => $this->description,
            'creationDate' => $this->creationDate,
        ];
    }
}
