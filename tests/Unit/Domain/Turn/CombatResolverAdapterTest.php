<?php

declare(strict_types=1);

use App\Domain\Combat\CombatResolver;
use App\Domain\Combat\Resolvers\AttackResolver;
use App\Domain\Turn\CombatResolverAdapter;
use App\Domain\Turn\CombatStateArrayMapper;
use App\Domain\Turn\TurnCombatResolverResult;
use Tests\Support\FakeDiceRoller;

beforeEach(function () {
    $this->mapper = new CombatStateArrayMapper;
    $this->combatResolver = new CombatResolver(
        new FakeDiceRoller(1),
        new AttackResolver
    );
    $this->adapter = new CombatResolverAdapter($this->combatResolver, $this->mapper);
});

it('converte array para CombatState, resolve e devolve array com estado atualizado', function () {
    $state = [
        'player' => ['hp' => 30, 'defense' => 10, 'damage' => 5],
        'enemy' => ['hp' => 20, 'defense' => 10, 'damage' => 4],
        'player_action' => 'attack',
    ];

    $result = $this->adapter->resolve(
        state: $state,
        dice: 15,
        npcDecision: ['action' => 'attack', 'damage' => 4]
    );

    expect($result->state['enemy']['hp'])->toBe(15)
        ->and($result->state['player']['hp'])->toBe(30)
        ->and($result->state['combat_ended'])->toBeFalse()
        ->and($result->state['combat_log'])->toContain('Ataque acertou causando 5 de dano.');
});

it('encerra combate e preenche combat_ended quando inimigo morre', function () {
    $state = [
        'player' => ['hp' => 30, 'defense' => 10, 'damage' => 50],
        'enemy' => ['hp' => 10, 'defense' => 10, 'damage' => 4],
        'player_action' => 'attack',
    ];

    $result = $this->adapter->resolve(
        state: $state,
        dice: 20,
        npcDecision: ['action' => 'defend']
    );

    expect($result->state['enemy']['hp'])->toBe(0)
        ->and($result->state['combat_ended'])->toBeTrue()
        ->and($result->state['combat_log'])->toContain('Inimigo derrotado.');
});

it('encerra combate quando jogador morre após ataque do NPC', function () {
    $adapter = new CombatResolverAdapter(
        new CombatResolver(new FakeDiceRoller(20), new AttackResolver),
        new CombatStateArrayMapper
    );

    $state = [
        'player' => ['hp' => 5, 'defense' => 10, 'damage' => 5],
        'enemy' => ['hp' => 20, 'defense' => 10, 'damage' => 10],
        'player_action' => 'attack',
    ];

    $result = $adapter->resolve(
        state: $state,
        dice: 1,
        npcDecision: ['action' => 'attack', 'damage' => 10]
    );

    expect($result->state['player']['hp'])->toBe(0)
        ->and($result->state['combat_ended'])->toBeTrue()
        ->and($result->state['combat_log'])->toContain('Jogador derrotado.');
});
