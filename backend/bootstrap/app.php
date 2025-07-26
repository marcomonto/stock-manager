<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (InvalidArgumentException $e, Request $request) {
            return response()->json([
                'message' => 'Invalid argument provided',
                'error' => $e->getMessage(),
                'type' => 'invalid_argument'
            ], 422);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'message' => 'The given data was invalid',
                'error' => $e->getMessage(),
                'type' => 'validation_error'
            ], 400);
        });

        $exceptions->render(function (\Exception $e, Request $request) {
            return response()->json([
                'message' => 'Internal server error',
            ], 500);
        });
    })->create();
