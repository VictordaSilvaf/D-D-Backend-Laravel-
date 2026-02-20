<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'D&D Backend API',
    description: 'API para gerenciamento de campanhas de Dungeons & Dragons'
)]
#[OA\Server(url: '/api/v1', description: 'API Server v1')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class AuthController extends Controller
{
    /**
     * Register a new user
     */
    #[OA\Post(
        path: '/auth/register',
        summary: 'Registrar novo usuário',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        tags: ['Autenticação'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuário registrado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                                        new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                                    ],
                                    type: 'object'
                                ),
                                new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
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
                response: 422,
                description: 'Erro de validação',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Os dados fornecidos são inválidos.'),
                        new OA\Property(
                            property: 'errors',
                            properties: [
                                new OA\Property(
                                    property: 'email',
                                    type: 'array',
                                    items: new OA\Items(type: 'string', example: 'O valor do campo e-mail já está em uso.')
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
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success(
            data: [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ],
            status: 201
        );
    }

    /**
     * Login a user
     */
    #[OA\Post(
        path: '/auth/login',
        summary: 'Realizar login',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        ),
        tags: ['Autenticação'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login realizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                                        new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                                    ],
                                    type: 'object'
                                ),
                                new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
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
                description: 'Credenciais inválidas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Credenciais inválidas.'),
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
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return ApiResponse::error(
                message: 'Credenciais inválidas.',
                status: 401
            );
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout the authenticated user
     */
    #[OA\Post(
        path: '/auth/logout',
        summary: 'Realizar logout',
        security: [['bearerAuth' => []]],
        tags: ['Autenticação'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout realizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'Logout realizado com sucesso.'),
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
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }

    /**
     * Logout the authenticated user from all devices
     */
    #[OA\Post(
        path: '/auth/logout-all',
        summary: 'Realizar logout de todos os dispositivos',
        security: [['bearerAuth' => []]],
        tags: ['Autenticação'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout de todos os dispositivos realizado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'message',
                                    type: 'string',
                                    example: 'Logout de todos os dispositivos realizado com sucesso.'
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
        ]
    )]
    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoga todos os tokens do usuário
        $user->tokens()->delete();

        return ApiResponse::success([
            'message' => 'Logout de todos os dispositivos realizado com sucesso.',
        ]);
    }

    /**
     * Get the authenticated user
     */
    #[OA\Get(
        path: '/auth/me',
        summary: 'Obter usuário autenticado',
        security: [['bearerAuth' => []]],
        tags: ['Autenticação'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dados do usuário',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                                        new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                                    ],
                                    type: 'object'
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
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }
}
