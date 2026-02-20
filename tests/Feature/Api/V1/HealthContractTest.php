<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GET /api/v1/health', function (): void {
    test('retorna status ok quando autenticado', function (): void {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['status', 'timestamp'],
                'meta' => ['api_version', 'request_id'],
            ])
            ->assertJson([
                'data' => [
                    'status' => 'ok',
                ],
                'meta' => [
                    'api_version' => '1',
                ],
            ]);

        expect($response->json('data.timestamp'))->toBeString();
    });

    test('retorna 401 quando não autenticado', function (): void {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(401)
            ->assertJsonStructure([
                'message',
                'meta' => ['api_version', 'request_id'],
            ])
            ->assertJson([
                'message' => 'Não autenticado.',
            ]);
    });
});
