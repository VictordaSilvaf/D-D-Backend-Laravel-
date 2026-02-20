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

test('processes a turn with valid action', function (): void {
    $session = GameSession::factory()->for($this->user)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")->postJson("/api/v1/game-sessions/{$session->id}/turn", [
        'player_action' => 'attack',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'dice' => [
                    'player',
                    'npc',
                ],
                'state' => [
                    'player',
                    'enemy',
                    'combat_log',
                ],
                'narration',
                'npc_decision',
                'combat_ended',
            ],
            'meta' => [
                'api_version',
                'request_id',
            ],
        ])
        ->assertJsonPath('data.combat_ended', false)
        ->assertJsonPath('meta.api_version', '1');

    $session->refresh();
    expect($session->state['combat_log'])->not->toBeEmpty();
});

test('processes turn with defend action', function (): void {
    $session = GameSession::factory()->for($this->user)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")->postJson("/api/v1/game-sessions/{$session->id}/turn", [
        'player_action' => 'defend',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'dice',
                'state',
                'narration',
                'npc_decision',
                'combat_ended',
            ],
        ])->assertJson([
            'data' => [
                'combat_ended' => false,
            ],
        ]);
});

test('processes turn with flee action', function (): void {
    $session = GameSession::factory()->for($this->user)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")->postJson("/api/v1/game-sessions/{$session->id}/turn", [
        'player_action' => 'flee',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'dice',
                'state',
                'narration',
                'npc_decision',
                'combat_ended',
            ],
        ])->assertJson([
            'data' => [
                'combat_ended' => true,
            ],
        ]);
});

test('processes turn with wait action', function (): void {
    $session = GameSession::factory()->for($this->user)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")->postJson("/api/v1/game-sessions/{$session->id}/turn", [
        'player_action' => 'wait',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'dice',
                'state',
                'narration',
                'npc_decision',
                'combat_ended',
            ],
        ])->assertJson([
            'data' => [
                'combat_ended' => false,
            ],
        ]);
});

test('returns validation error for invalid action', function (): void {
    $session = GameSession::factory()->for($this->user)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")->postJson("/api/v1/game-sessions/{$session->id}/turn", [
        'player_action' => 'invalid_action',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => [
                'player_action',
            ],
            'meta' => [
                'api_version',
                'request_id',
            ],
        ]);
});

test('returns error when combat already ended', function (): void {
    $session = GameSession::factory()->for($this->user)->create([
        'combat_ended' => true,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")->postJson("/api/v1/game-sessions/{$session->id}/turn", [
        'player_action' => 'attack',
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'message' => 'Combat already ended.',
        ]);
});

test('returns unauthenticated error when processing turn without token', function (): void {
    $session = GameSession::factory()->for($this->user)->create();

    $response = $this->postJson("/api/v1/game-sessions/{$session->id}/turn", [
        'player_action' => 'attack',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Não autenticado.',
        ]);
});

test('returns forbidden when processing turn on another user session', function (): void {
    $otherUser = User::factory()->create();
    $session = GameSession::factory()->for($otherUser)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")->postJson("/api/v1/game-sessions/{$session->id}/turn", [
        'player_action' => 'attack',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'This action is unauthorized.',
        ]);
});

test('returns not found when processing turn on nonexistent session', function (): void {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")->postJson('/api/v1/game-sessions/99999/turn', [
        'player_action' => 'attack',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'message' => 'Recurso não encontrado.',
        ]);
});

test('updates session state after processing turn', function (): void {
    $session = GameSession::factory()->for($this->user)->create([
        'state' => [
            'player' => ['hp' => 30, 'defense' => 10, 'damage' => 5],
            'enemy' => ['hp' => 50, 'defense' => 10, 'damage' => 15],
            'combat_log' => [],
        ],
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")->postJson("/api/v1/game-sessions/{$session->id}/turn", [
        'player_action' => 'attack',
    ]);

    $response->assertStatus(200);

    $session->refresh();

    expect($session->state['combat_log'])->not->toBeEmpty();
    expect($session->updated_at->diffInSeconds(now()))->toBeLessThan(5);
});
