<?php

declare(strict_types=1);

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

test('creates a new game session with valid data', function (): void {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/game-sessions', [
            'enemy' => [
                'hp' => 50,
                'defense' => 10,
                'damage' => 15,
            ],
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'state' => [
                    'player',
                    'enemy',
                    'combat_log',
                ],
                'combat_ended',
                'created_at',
                'updated_at',
            ],
            'meta' => [
                'api_version',
                'request_id',
            ],
        ])
        ->assertJson([
            'data' => [
                'user_id' => $this->user->id,
                'state' => [
                    'player' => [
                        'hp' => 30,
                        'defense' => 10,
                        'damage' => 5,
                    ],
                    'enemy' => [
                        'hp' => 50,
                        'defense' => 10,
                        'damage' => 15,
                    ],
                ],
                'combat_ended' => false,
            ],
            'meta' => [
                'api_version' => '1',
            ],
        ]);

    $this->assertDatabaseHas('game_sessions', [
        'user_id' => $this->user->id,
        'combat_ended' => false,
    ]);
});

test('returns validation error when enemy data is invalid', function (): void {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/game-sessions', [
            'enemy' => [
                'hp' => 0, // Invalid: below minimum
                'defense' => 50, // Invalid: above maximum
                'damage' => -5, // Invalid: negative
            ],
        ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => [
                'enemy.hp',
                'enemy.defense',
                'enemy.damage',
            ],
            'meta' => [
                'api_version',
                'request_id',
            ],
        ]);
});

test('returns unauthenticated error when creating without token', function (): void {
    $response = $this->postJson('/api/v1/game-sessions', [
        'enemy' => [
            'hp' => 50,
            'defense' => 10,
            'damage' => 15,
        ],
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Não autenticado.',
        ]);
});

test('shows game session details', function (): void {
    $session = GameSession::factory()->for($this->user)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/game-sessions/{$session->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'state',
                'combat_ended',
                'created_at',
                'updated_at',
            ],
            'meta' => [
                'api_version',
                'request_id',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => $session->id,
                'user_id' => $this->user->id,
            ],
        ]);
});

test('returns forbidden when viewing another user session', function (): void {
    $otherUser = User::factory()->create();
    $session = GameSession::factory()->for($otherUser)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/game-sessions/{$session->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
});

test('returns not found when viewing nonexistent session', function (): void {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/game-sessions/99999');

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Recurso não encontrado.',
        ]);
});

test('deletes a game session', function (): void {
    $session = GameSession::factory()->for($this->user)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson("/api/v1/game-sessions/{$session->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'message' => 'Session deleted successfully.',
            ],
        ]);

    $this->assertDatabaseMissing('game_sessions', [
        'id' => $session->id,
    ]);
});

test('returns forbidden when deleting another user session', function (): void {
    $otherUser = User::factory()->create();
    $session = GameSession::factory()->for($otherUser)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson("/api/v1/game-sessions/{$session->id}");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'This action is unauthorized.',
        ]);

    $this->assertDatabaseHas('game_sessions', [
        'id' => $session->id,
    ]);
});

test('gets combat logs from a session', function (): void {
    $session = GameSession::factory()->for($this->user)->create([
        'state' => [
            'player' => ['hp' => 30, 'defense' => 10, 'damage' => 5],
            'enemy' => ['hp' => 50, 'defense' => 10, 'damage' => 15],
            'combat_log' => [
                'Jogador atacou causando 15 de dano!',
                'Inimigo defendeu!',
            ],
        ],
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/game-sessions/{$session->id}/logs");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'logs',
            ],
            'meta' => [
                'api_version',
                'request_id',
            ],
        ])
        ->assertJson([
            'data' => [
                'logs' => [
                    'Jogador atacou causando 15 de dano!',
                    'Inimigo defendeu!',
                ],
            ],
        ]);
});

test('returns forbidden when viewing logs from another user session', function (): void {
    $otherUser = User::factory()->create();
    $session = GameSession::factory()->for($otherUser)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/game-sessions/{$session->id}/logs");

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
});