<?php

namespace App\Providers;

use App\UnitOfWork\LaravelQueryBuilderUOW;
use App\UnitOfWork\UnitOfWork;
use Illuminate\Support\ServiceProvider;

class InterfaceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UnitOfWork::class, LaravelQueryBuilderUOW::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
