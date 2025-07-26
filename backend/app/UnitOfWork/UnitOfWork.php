<?php

namespace App\UnitOfWork;

interface UnitOfWork
{
    public function begin(): void;

    public function save(): void;

    public function discard(): void;
}
