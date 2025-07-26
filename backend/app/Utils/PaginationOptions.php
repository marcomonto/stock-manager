<?php

namespace App\Utils;

readonly class PaginationOptions
{
    public function __construct(
        public int $page,
        public int $rowsPerPage,
    ) {}
}
