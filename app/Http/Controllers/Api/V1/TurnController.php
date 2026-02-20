<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\GameSessionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\GameSession\ProcessTurnRequest;
use App\Http\Responses\ApiResponse;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class TurnController extends Controller
{
    public function __construct(
        private readonly GameSessionService $gameSessionService
    ) {}

    /**
     * Process a turn
     */
    #[OA\Post(
        path: '/game-sessions/{id}/turn',
        summary: 'Processar um turno de combate',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['player_action'],
                properties: [
                    new OA\Property(
                        property: 'player_action',
                        type: 'string',
                        enum: ['attack', 'defend', 'flee', 'wait'],
                        example: 'attack',
                        description: 'Ação do jogador no turno'
                    ),
                ]
            )
        ),
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
                description: 'Turno processado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'dice',
                                    properties: [
                                        new OA\Property(property: 'player', type: 'integer', example: 15),
                                        new OA\Property(property: 'npc', type: 'integer', example: 8),
                                    ],
                                    type: 'object'
                                ),
                                new OA\Property(
                                    property: 'state',
                                    properties: [
                                        new OA\Property(
                                            property: 'player',
                                            properties: [
                                                new OA\Property(property: 'hp', type: 'integer', example: 25),
                                                new OA\Property(property: 'defense', type: 'integer', example: 10),
                                                new OA\Property(property: 'damage', type: 'integer', example: 5),
                                            ],
                                            type: 'object'
                                        ),
                                        new OA\Property(
                                            property: 'enemy',
                                            properties: [
                                                new OA\Property(property: 'hp', type: 'integer', example: 35),
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
                                new OA\Property(
                                    property: 'narration',
                                    type: 'string',
                                    example: 'Jogador atacou causando 15 de dano!'
                                ),
                                new OA\Property(
                                    property: 'npc_decision',
                                    type: 'string',
                                    enum: ['attack', 'defend', 'flee', 'wait'],
                                    example: 'attack'
                                ),
                                new OA\Property(
                                    property: 'combat_ended',
                                    type: 'boolean',
                                    example: false
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
                response: 400,
                description: 'Combate já encerrado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'O combate já foi encerrado.'),
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
                                    property: 'player_action',
                                    type: 'array',
                                    items: new OA\Items(type: 'string', example: 'A ação do jogador deve ser uma das seguintes: attack, defend, flee, wait.')
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
    public function process(int $id, ProcessTurnRequest $request): JsonResponse
    {
        $gameSession = GameSession::findOrFail($id);
        $this->authorize('update', $gameSession);

        $validated = $request->validated();

        try {
            $result = $this->gameSessionService->processTurn(
                session: $gameSession,
                playerAction: $validated['player_action']
            );

            return ApiResponse::success(data: [
                'dice' => $result->dice,
                'state' => $result->updatedState,
                'narration' => $result->narration,
                'npc_decision' => $result->npcDecision,
                'combat_ended' => $result->updatedState['combat_ended'] ?? false,
            ]);
        } catch (\RuntimeException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                status: 400
            );
        }
    }
}
