<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('API Error Handling', function (): void {
    test('retorna 404 com JSON padronizado para rota inexistente', function (): void {
        $response = $this->getJson('/api/v1/nonexistent-route');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'message',
                'meta' => ['api_version', 'request_id'],
            ])
            ->assertJson([
                'message' => 'Recurso não encontrado.',
                'meta' => [
                    'api_version' => '1',
                ],
            ]);
    });

    test('retorna 422 com estrutura de erros correta para validação', function (): void {
        // Testing via register endpoint which requires validation
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123', // too short
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors',
                'meta' => ['api_version', 'request_id'],
            ])
            ->assertJson([
                'meta' => [
                    'api_version' => '1',
                ],
            ]);

        // Verify errors is an object with arrays
        expect($response->json('errors'))->toBeArray();
    });

    test('retorna 401 com JSON padronizado para não autenticado', function (): void {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'meta' => ['api_version', 'request_id'],
            ])
            ->assertJson([
                'message' => 'Não autenticado.',
                'meta' => [
                    'api_version' => '1',
                ],
            ]);
    });

    test('response sempre inclui request_id no header', function (): void {
        $response = $this->getJson('/api/v1/nonexistent');

        $response->assertHeader('X-Request-ID');

        $requestId = $response->headers->get('X-Request-ID');
        expect($requestId)->toBeString();
        expect(strlen($requestId))->toBeGreaterThan(0);
    });

    test('request_id no meta corresponde ao header', function (): void {
        $response = $this->getJson('/api/v1/health');

        $headerId = $response->headers->get('X-Request-ID');
        $metaId = $response->json('meta.request_id');

        expect($headerId)->toBe($metaId);
    });
});
