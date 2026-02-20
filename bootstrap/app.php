<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add RequestIdMiddleware to API routes
        $middleware->group('api', [
            \App\Http\Middleware\RequestIdMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle exceptions for API requests
        $exceptions->render(function (Throwable $e, Request $request) {
            // Only apply custom error handling for API routes
            if (! $request->is('api/*')) {
                return null;
            }

            // Ensure request_id exists for API errors
            if (! $request->attributes->has('request_id')) {
                $requestId = (string) \Illuminate\Support\Str::uuid();
                $request->attributes->set('request_id', $requestId);
                \Illuminate\Support\Facades\Log::withContext([
                    'request_id' => $requestId,
                ]);
            }

            // ValidationException - 422
            if ($e instanceof ValidationException) {
                $response = ApiResponse::error(
                    message: 'Os dados fornecidos são inválidos.',
                    errors: $e->errors(),
                    status: 422
                );
                $response->header('X-Request-ID', $request->attributes->get('request_id'));

                return $response;
            }

            // AuthenticationException - 401
            if ($e instanceof AuthenticationException) {
                $response = ApiResponse::error(
                    message: 'Não autenticado.',
                    status: 401
                );
                $response->header('X-Request-ID', $request->attributes->get('request_id'));

                return $response;
            }

            // ModelNotFoundException or NotFoundHttpException - 404
            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                $response = ApiResponse::error(
                    message: 'Recurso não encontrado.',
                    status: 404
                );
                $response->header('X-Request-ID', $request->attributes->get('request_id'));

                return $response;
            }

            // Generic error - 500
            $message = config('app.debug')
                ? $e->getMessage()
                : 'Erro interno do servidor.';

            $status = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : 500;

            $response = ApiResponse::error(
                message: $message,
                status: $status
            );
            $response->header('X-Request-ID', $request->attributes->get('request_id'));

            return $response;
        });
    })->create();
