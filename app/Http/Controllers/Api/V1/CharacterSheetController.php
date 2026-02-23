<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Services\CharacterSheetPdfParser;
use App\Application\Services\CharacterSheetService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CharacterSheet\ImportCharacterSheetPdfRequest;
use App\Http\Requests\CharacterSheet\StoreCharacterSheetRequest;
use App\Http\Requests\CharacterSheet\UpdateCharacterSheetRequest;
use App\Http\Requests\CharacterSheet\UpdateCharacterSheetStepRequest;
use App\Http\Responses\ApiResponse;
use App\Models\CharacterSheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CharacterSheetController extends Controller
{
    public function __construct(
        private readonly CharacterSheetService $characterSheetService,
        private readonly CharacterSheetPdfParser $pdfParser,
    ) {}

    /**
     * List character sheets for the authenticated user.
     */
    #[OA\Get(
        path: '/character-sheets',
        summary: 'Listar fichas de personagem do usuário',
        security: [['bearerAuth' => []]],
        tags: ['Character Sheets'],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filtrar por status (draft ou completed)',
                schema: new OA\Schema(type: 'string', enum: ['draft', 'completed'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de fichas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'status', type: 'string', example: 'draft'),
                                    new OA\Property(property: 'state', type: 'object'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object'
                            )
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
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $sheets = $this->characterSheetService->listForUser(
            $request->user()->id,
            $status ? (string) $status : null
        );

        return ApiResponse::success($sheets->toArray());
    }

    /**
     * Create a new character sheet (draft).
     */
    #[OA\Post(
        path: '/character-sheets',
        summary: 'Criar nova ficha de personagem (rascunho)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'state',
                        type: 'object',
                        description: 'Estado inicial da ficha (basics, class, abilities, etc.)',
                        properties: [
                            new OA\Property(property: 'basics', type: 'object'),
                            new OA\Property(property: 'class', type: 'object'),
                            new OA\Property(property: 'abilities', type: 'object'),
                            new OA\Property(property: 'background', type: 'object'),
                            new OA\Property(property: 'combat', type: 'object'),
                            new OA\Property(property: 'equipment', type: 'object'),
                        ]
                    ),
                ]
            )
        ),
        tags: ['Character Sheets'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Ficha criada com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                new OA\Property(property: 'status', type: 'string', example: 'draft'),
                                new OA\Property(property: 'state', type: 'object'),
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
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 422, description: 'Erro de validação'),
        ]
    )]
    public function store(StoreCharacterSheetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $state = $validated['state'] ?? [];

        $sheet = $this->characterSheetService->create($request->user()->id, $state);

        return ApiResponse::success($sheet->toArray(), 201);
    }

    /**
     * Get a single character sheet.
     */
    #[OA\Get(
        path: '/character-sheets/{id}',
        summary: 'Obter uma ficha de personagem',
        security: [['bearerAuth' => []]],
        tags: ['Character Sheets'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID da ficha', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ficha encontrada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                new OA\Property(property: 'status', type: 'string', example: 'draft'),
                                new OA\Property(property: 'state', type: 'object'),
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
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 403, description: 'Acesso negado'),
            new OA\Response(response: 404, description: 'Ficha não encontrada'),
        ]
    )]
    public function show(CharacterSheet $characterSheet): JsonResponse
    {
        $this->authorize('view', $characterSheet);

        return ApiResponse::success($characterSheet->toArray());
    }

    /**
     * Update character sheet (full or partial state).
     */
    #[OA\Put(
        path: '/character-sheets/{id}',
        summary: 'Atualizar ficha de personagem',
        security: [['bearerAuth' => []]],
        tags: ['Character Sheets'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID da ficha', schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'state',
                        type: 'object',
                        description: 'Estado (parcial ou completo). Ficha finalizada não pode ser alterada.',
                        properties: [
                            new OA\Property(property: 'basics', type: 'object'),
                            new OA\Property(property: 'class', type: 'object'),
                            new OA\Property(property: 'abilities', type: 'object'),
                            new OA\Property(property: 'background', type: 'object'),
                            new OA\Property(property: 'combat', type: 'object'),
                            new OA\Property(property: 'equipment', type: 'object'),
                        ]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ficha atualizada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object', description: 'Ficha atualizada'),
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
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 403, description: 'Acesso negado'),
            new OA\Response(response: 404, description: 'Ficha não encontrada'),
            new OA\Response(response: 422, description: 'Ficha já finalizada ou erro de validação'),
        ]
    )]
    public function update(UpdateCharacterSheetRequest $request, CharacterSheet $characterSheet): JsonResponse
    {
        $this->authorize('update', $characterSheet);

        if ($characterSheet->isCompleted()) {
            return ApiResponse::error('Ficha já finalizada e não pode ser alterada.', null, 422);
        }

        $validated = $request->validated();
        $state = $validated['state'] ?? [];

        $sheet = $this->characterSheetService->update($characterSheet, $state);

        return ApiResponse::success($sheet->toArray());
    }

    /**
     * Update a single step of the wizard.
     */
    #[OA\Patch(
        path: '/character-sheets/{id}/step/{step}',
        summary: 'Atualizar uma etapa do wizard',
        security: [['bearerAuth' => []]],
        tags: ['Character Sheets'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID da ficha', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(
                name: 'step',
                in: 'path',
                required: true,
                description: 'Nome da etapa',
                schema: new OA\Schema(type: 'string', enum: ['basics', 'class', 'abilities', 'background', 'combat', 'equipment', 'review'])
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['state'],
                properties: [
                    new OA\Property(property: 'state', type: 'object', description: 'Dados da etapa'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Etapa atualizada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object', description: 'Ficha atualizada'),
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
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 403, description: 'Acesso negado'),
            new OA\Response(response: 404, description: 'Ficha não encontrada'),
            new OA\Response(response: 422, description: 'Ficha finalizada, etapa inválida ou erro de validação'),
        ]
    )]
    public function updateStep(
        UpdateCharacterSheetStepRequest $request,
        CharacterSheet $characterSheet,
        string $step
    ): JsonResponse {
        $this->authorize('update', $characterSheet);

        if ($characterSheet->isCompleted()) {
            return ApiResponse::error('Ficha já finalizada e não pode ser alterada.', null, 422);
        }

        $validSteps = ['basics', 'class', 'abilities', 'background', 'combat', 'equipment', 'review'];
        if (! in_array($step, $validSteps, true)) {
            return ApiResponse::error('Etapa inválida.', ['step' => ['Etapa deve ser uma de: '.implode(', ', $validSteps)]], 422);
        }

        $validated = $request->validated();
        $sheet = $this->characterSheetService->updateStep($characterSheet, $step, $validated['state']);

        return ApiResponse::success($sheet->toArray());
    }

    /**
     * Mark character sheet as completed.
     */
    #[OA\Post(
        path: '/character-sheets/{id}/complete',
        summary: 'Finalizar ficha de personagem',
        security: [['bearerAuth' => []]],
        tags: ['Character Sheets'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID da ficha', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ficha finalizada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'object', description: 'Ficha com status completed'),
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
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 403, description: 'Acesso negado'),
            new OA\Response(response: 404, description: 'Ficha não encontrada'),
        ]
    )]
    public function complete(CharacterSheet $characterSheet): JsonResponse
    {
        $this->authorize('update', $characterSheet);

        $sheet = $this->characterSheetService->complete($characterSheet);

        return ApiResponse::success($sheet->toArray());
    }

    /**
     * Delete a character sheet.
     */
    #[OA\Delete(
        path: '/character-sheets/{id}',
        summary: 'Remover ficha de personagem',
        security: [['bearerAuth' => []]],
        tags: ['Character Sheets'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID da ficha', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ficha removida com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'Ficha removida com sucesso.'),
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
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 403, description: 'Acesso negado'),
            new OA\Response(response: 404, description: 'Ficha não encontrada'),
        ]
    )]
    public function destroy(CharacterSheet $characterSheet): JsonResponse
    {
        $this->authorize('delete', $characterSheet);

        $characterSheet->delete();

        return ApiResponse::success(['message' => 'Ficha removida com sucesso.']);
    }

    /**
     * Import character sheet from PDF upload. Returns parsed state (and optionally creates a new sheet).
     */
    #[OA\Post(
        path: '/character-sheets/import-pdf',
        summary: 'Importar ficha de personagem a partir de PDF',
        security: [['bearerAuth' => []]],
        tags: ['Character Sheets'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(
                            property: 'file',
                            type: 'string',
                            format: 'binary',
                            description: 'Arquivo PDF da ficha (máx. 5 MB)'
                        ),
                        new OA\Property(
                            property: 'create',
                            type: 'boolean',
                            default: true,
                            description: 'Se true (padrão), cria uma nova ficha com os dados extraídos'
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Apenas dados extraídos (create=false)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'parsed', type: 'object', description: 'Estado extraído do PDF'),
                                new OA\Property(property: 'unrecognized', type: 'array', items: new OA\Items(type: 'string')),
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
                response: 201,
                description: 'Ficha criada a partir do PDF (create=true)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'character_sheet', type: 'object', description: 'Ficha criada'),
                                new OA\Property(property: 'parsed', type: 'object', description: 'Estado extraído'),
                                new OA\Property(property: 'unrecognized', type: 'array', items: new OA\Items(type: 'string')),
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
            new OA\Response(response: 401, description: 'Não autenticado'),
            new OA\Response(response: 422, description: 'Arquivo inválido ou ausente'),
        ]
    )]
    public function importPdf(ImportCharacterSheetPdfRequest $request): JsonResponse
    {
        $file = $request->file('file');
        if ($file === null) {
            return ApiResponse::error('Arquivo não enviado.', null, 422);
        }

        $parsed = $this->pdfParser->parse($file->getRealPath());

        $create = $request->boolean('create', true);
        if ($create) {
            $sheet = $this->characterSheetService->createFromImport($request->user()->id, $parsed['state']);

            return ApiResponse::success([
                'character_sheet' => $sheet->toArray(),
                'parsed' => $parsed,
                'unrecognized' => $parsed['unrecognized'] ?? [],
            ], 201);
        }

        return ApiResponse::success([
            'parsed' => $parsed,
            'unrecognized' => $parsed['unrecognized'] ?? [],
        ], 200);
    }
}
