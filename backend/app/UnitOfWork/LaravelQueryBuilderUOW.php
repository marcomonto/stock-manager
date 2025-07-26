<?php

namespace App\UnitOfWork;

use Illuminate\Support\Facades\DB;

class LaravelQueryBuilderUOW implements UnitOfWork
{
    public function begin(): void
    {
        DB::beginTransaction();
    }

    public function save(): void
    {
        DB::commit();
    }

    public function discard(): void
    {
        DB::rollBack();
    }
}
