<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    /**
     * Get the health status of the API
     */
    #[OA\Get(
        path: '/health',
        summary: 'Verificar saúde da API',
        security: [['bearerAuth' => []]],
        tags: ['Sistema'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'API funcionando normalmente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'status', type: 'string', example: 'ok'),
                                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time', example: '2026-02-18T10:30:00+00:00'),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'api_version', type: 'string', example: '1'),
                                new OA\Property(property: 'request_id', type: 'string', format: 'uuid'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Não autenticado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Não autenticado.'),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'api_version', type: 'string', example: '1'),
                                new OA\Property(property: 'request_id', type: 'string', format: 'uuid'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
