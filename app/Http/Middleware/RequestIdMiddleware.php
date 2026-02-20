<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a unique request ID
        $requestId = (string) Str::uuid();

        // Store in request attributes for access throughout the request lifecycle
        $request->attributes->set('request_id', $requestId);

        // Add request ID to log context
        Log::withContext([
            'request_id' => $requestId,
        ]);

        // Process the request
        $response = $next($request);

        // Add request ID to response headers
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
