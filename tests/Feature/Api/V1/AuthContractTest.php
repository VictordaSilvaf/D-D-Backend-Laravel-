<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('POST /api/v1/auth/register', function (): void {
    test('registra um novo usuário com sucesso', function (): void {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
                'meta' => ['api_version', 'request_id'],
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'name' => 'Test User',
                        'email' => 'test@example.com',
                    ],
                ],
                'meta' => [
                    'api_version' => '1',
                ],
            ]);

        expect($response->json('data.token'))->toBeString();
        expect(User::query()->where('email', 'test@example.com')->exists())->toBeTrue();
    });

    test('retorna 422 com erros de validação ao tentar registrar sem dados obrigatórios', function (): void {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['name', 'email', 'password'],
                'meta' => ['api_version', 'request_id'],
            ]);
    });

    test('retorna 422 quando o email já está em uso', function (): void {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
                'meta' => ['api_version'],
            ]);
    });
});

describe('POST /api/v1/auth/login', function (): void {
    test('realiza login com credenciais válidas', function (): void {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token',
                ],
                'meta' => ['api_version', 'request_id'],
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => 'user@example.com',
                    ],
                ],
            ]);

        expect($response->json('data.token'))->toBeString();
    });

    test('retorna 401 com credenciais inválidas', function (): void {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'meta' => ['api_version', 'request_id'],
            ])
            ->assertJson([
                'message' => 'Credenciais inválidas.',
            ]);
    });

    test('retorna 422 quando dados de login são inválidos', function (): void {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email', 'password'],
                'meta' => ['api_version'],
            ]);
    });
});

describe('GET /api/v1/auth/me', function (): void {
    test('retorna dados do usuário autenticado', function (): void {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                ],
                'meta' => ['api_version', 'request_id'],
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ]);
    });

    test('retorna 401 quando não autenticado', function (): void {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'meta' => ['api_version', 'request_id'],
            ]);
    });
});

describe('POST /api/v1/auth/logout', function (): void {
    test('realiza logout do usuário autenticado', function (): void {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['message'],
                'meta' => ['api_version', 'request_id'],
            ]);

        // Verify token was deleted
        expect($user->tokens()->count())->toBe(0);
    });

    test('retorna 401 quando não autenticado', function (): void {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'meta' => ['api_version'],
            ]);
    });
});
