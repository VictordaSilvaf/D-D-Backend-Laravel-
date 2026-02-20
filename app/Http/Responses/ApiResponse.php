<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Create a successful API response
     */
    public static function success(mixed $data, int $status = 200): JsonResponse
    {
        $response = [
            'data' => $data,
            'meta' => self::buildMeta(),
        ];

        return response()->json($response, $status);
    }

    /**
     * Create an error API response
     */
    public static function error(string $message, mixed $errors = null, int $status = 500): JsonResponse
    {
        $response = [
            'message' => $message,
            'meta' => self::buildMeta(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Build the meta information for responses
     *
     * @return array<string, mixed>
     */
    private static function buildMeta(): array
    {
        $meta = [
            'api_version' => config('api.version'),
        ];

        // Add request_id if available
        $request = request();
        if ($request && $request->attributes->has('request_id')) {
            $meta['request_id'] = $request->attributes->get('request_id');
        }

        return $meta;
    }
}
