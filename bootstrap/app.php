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

        $exceptions->render(function (Throwable $e, Request $request) {

            if (! $request->is('api/*')) {
                return null;
            }

            $requestId = $request->attributes->get('request_id')
                ?? (string) \Illuminate\Support\Str::uuid();

            $request->attributes->set('request_id', $requestId);

            \Illuminate\Support\Facades\Log::withContext([
                'request_id' => $requestId,
            ]);

            $isDebug = config('app.debug');

            $buildResponse = function (
                string $message,
                int $status,
                ?array $errors = null
            ) use ($e, $isDebug, $requestId) {

                $payload = [
                    'message' => $message,
                ];

                if ($errors) {
                    $payload['errors'] = $errors;
                }

                if ($isDebug) {
                    $payload['debug'] = [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->take(15),
                    ];
                }

                $response = response()->json($payload, $status);
                $response->header('X-Request-ID', $requestId);

                return $response;
            };

            // 422
            if ($e instanceof ValidationException) {
                return $buildResponse(
                    'Os dados fornecidos são inválidos.',
                    422,
                    $e->errors()
                );
            }

            // 401
            if ($e instanceof AuthenticationException) {
                return $buildResponse(
                    'Não autenticado.',
                    401
                );
            }

            // 404
            if (
                $e instanceof ModelNotFoundException ||
                $e instanceof NotFoundHttpException
            ) {
                return $buildResponse(
                    'Recurso não encontrado.',
                    404
                );
            }

            // 500 ou HTTP Exception
            $status = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : 500;

            $message = $isDebug
                ? $e->getMessage()
                : 'Erro interno do servidor.';

            return $buildResponse($message, $status);
        });
    })->create();
