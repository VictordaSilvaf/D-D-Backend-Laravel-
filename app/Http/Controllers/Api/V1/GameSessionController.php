<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\GameSessionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameSession\CreateGameSessionRequest;
use App\Http\Responses\ApiResponse;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class GameSessionController extends Controller
{
    public function __construct(
        private readonly GameSessionService $gameSessionService
    ) {}

    /**
     * Create a new game session
     */
    #[OA\Post(
        path: '/game-sessions',
        summary: 'Criar nova sessão de jogo',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['enemy'],
                properties: [
                    new OA\Property(
                        property: 'enemy',
                        properties: [
                            new OA\Property(property: 'hp', type: 'integer', example: 50, description: 'HP do inimigo (1-1000)'),
                            new OA\Property(property: 'defense', type: 'integer', example: 8, description: 'Defesa do inimigo (1-30)'),
                            new OA\Property(property: 'damage', type: 'integer', example: 10, description: 'Dano do inimigo (1-100)'),
                        ],
                        type: 'object'
                    ),
                ]
            )
        ),
        tags: ['Game Sessions'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Sessão criada com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                new OA\Property(
                                    property: 'state',
                                    properties: [
                                        new OA\Property(
                                            property: 'player',
                                            properties: [
                                                new OA\Property(property: 'hp', type: 'integer', example: 30),
                                                new OA\Property(property: 'defense', type: 'integer', example: 10),
                                                new OA\Property(property: 'damage', type: 'integer', example: 5),
                                            ],
                                            type: 'object'
                                        ),
                                        new OA\Property(
                                            property: 'enemy',
                                            properties: [
                                                new OA\Property(property: 'hp', type: 'integer', example: 50),
                                                new OA\Property(property: 'defense', type: 'integer', example: 8),
                                                new OA\Property(property: 'damage', type: 'integer', example: 10),
                                            ],
                                            type: 'object'
                                        ),
                                        new OA\Property(
                                            property: 'combat_log',
                                            type: 'array',
                                            items: new OA\Items(type: 'string')
                                        ),
                                    ],
                                    type: 'object'
                                ),
                                new OA\Property(property: 'combat_ended', type: 'boolean', example: false),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
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
            new OA\Response(
                response: 422,
                description: 'Erro de validação',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Os dados fornecidos são inválidos.'),
                        new OA\Property(
                            property: 'errors',
                            properties: [
                                new OA\Property(
                                    property: 'enemy.hp',
                                    type: 'array',
                                    items: new OA\Items(type: 'string', example: 'O campo HP do inimigo deve ser no mínimo 1.')
                                ),
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
        ]
    )]
    public function store(CreateGameSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $session = $this->gameSessionService->createSession(
            userId: $request->user()->id,
            enemyStats: $validated['enemy']
        );

        return ApiResponse::success(
            data: $session->toArray(),
            status: 201
        );
    }

    /**
     * Get session details
     */
    #[OA\Get(
        path: '/game-sessions/{id}',
        summary: 'Obter detalhes de uma sessão',
        security: [['bearerAuth' => []]],
        tags: ['Game Sessions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID da sessão de jogo',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalhes da sessão',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                new OA\Property(property: 'state', type: 'object'),
                                new OA\Property(property: 'combat_ended', type: 'boolean', example: false),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
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
            new OA\Response(
                response: 403,
                description: 'Acesso negado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Esta ação não é autorizada.'),
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
                response: 404,
                description: 'Sessão não encontrada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Recurso não encontrado.'),
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
    public function show(int $id): JsonResponse
    {
        $gameSession = GameSession::findOrFail($id);
        $this->authorize('view', $gameSession);

        return ApiResponse::success(
            data: $gameSession->toArray()
        );
    }

    /**
     * Delete a game session
     */
    #[OA\Delete(
        path: '/game-sessions/{id}',
        summary: 'Deletar uma sessão de jogo',
        security: [['bearerAuth' => []]],
        tags: ['Game Sessions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID da sessão de jogo',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sessão deletada com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'Sessão deletada com sucesso.'),
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
            new OA\Response(
                response: 403,
                description: 'Acesso negado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Esta ação não é autorizada.'),
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
                response: 404,
                description: 'Sessão não encontrada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Recurso não encontrado.'),
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
    public function destroy(int $id): JsonResponse
    {
        $gameSession = GameSession::findOrFail($id);
        $this->authorize('delete', $gameSession);

        $gameSession->delete();

        return ApiResponse::success(
            data: ['message' => 'Session deleted successfully.']
        );
    }

    /**
     * Get combat logs
     */
    #[OA\Get(
        path: '/game-sessions/{id}/logs',
        summary: 'Obter histórico de combate',
        security: [['bearerAuth' => []]],
        tags: ['Game Sessions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID da sessão de jogo',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Histórico de combate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'logs',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'string',
                                        example: 'Jogador atacou causando 15 de dano!'
                                    )
                                ),
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
            new OA\Response(
                response: 403,
                description: 'Acesso negado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Esta ação não é autorizada.'),
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
                response: 404,
                description: 'Sessão não encontrada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Recurso não encontrado.'),
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
    public function logs(int $id): JsonResponse
    {
        $gameSession = GameSession::findOrFail($id);
        $this->authorize('view', $gameSession);

        $logs = $this->gameSessionService->getLogs($gameSession);

        return ApiResponse::success(
            data: ['logs' => $logs]
        );
    }
}
