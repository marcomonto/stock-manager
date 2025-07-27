<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        // commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $dontLog = [
            InvalidArgumentException::class,
            NotFoundHttpException::class,
            MethodNotAllowedHttpException::class,
            ValidationException::class,
        ];
        $exceptions->dontReport($dontLog);
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'message' => 'Route not found',
                'error' => 'The requested resource could not be found',
                'type' => 'not_found',
            ], 404);
        });
        $exceptions->render(function (InvalidArgumentException $e, Request $request) {
            return response()->json([
                'message' => 'Invalid argument provided',
                'error' => $e->getMessage(),
                'type' => 'invalid_argument',
            ], 422);
        });
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            return response()->json([
                'message' => 'Method not allowed',
                'error' => 'The '.$request->method().' method is not allowed for this route',
                'type' => 'method_not_allowed',
                'allowed_methods' => $e->getHeaders()['Allow'] ?? [],
            ], 405);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'message' => 'The given data was invalid',
                'error' => $e->getMessage(),
                'type' => 'validation_error',
            ], 400);
        });

        $exceptions->render(function (\Exception $e, Request $request) {
            \Illuminate\Support\Facades\Log::error($e->getMessage());
            return response()->json([
                'message' => 'Internal server error',
            ], 500);
        });
    })->create();
