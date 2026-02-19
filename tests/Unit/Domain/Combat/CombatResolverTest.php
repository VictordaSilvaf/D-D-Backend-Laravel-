<?php

declare(strict_types=1);

use App\Domain\Combat\Character;
use App\Domain\Combat\CombatResolver;
use App\Domain\Combat\CombatState;
use App\Domain\Combat\Enums\ActionType;
use App\Domain\Combat\NpcDecision;
use Tests\Support\FakeDiceRoller;

it('player acerta ataque', function () {

    $state = makeState();

    $resolver = new CombatResolver(
        new FakeDiceRoller(1)
    );

    $npcDecision = new NpcDecision(
        action: ActionType::Attack,
        damage: 4
    );

    $result = $resolver->resolve(
        state: $state,
        playerDice: 15,
        npcDecision: $npcDecision
    );

    expect($result->enemy->hp)->toBe(15);
});

it('encerra combate quando inimigo morre', function () {

    $state = makeState(
        playerHp: 30,
        enemyHp: 10,
        playerDamage: 50
    );

    $resolver = new CombatResolver(
        new FakeDiceRoller(1)
    );

    $npcDecision = new NpcDecision(
        action: ActionType::Attack,
        damage: 4
    );

    $result = $resolver->resolve(
        state: $state,
        playerDice: 20,
        npcDecision: $npcDecision
    );

    expect($result->enemy->isAlive())->toBeFalse()
        ->and($result->combatEnded)->toBeTrue();
});

it('npc acerta ataque', function () {

    $state = makeState();

    $resolver = new CombatResolver(
        new FakeDiceRoller(20)
    );

    $npcDecision = new NpcDecision(
        action: ActionType::Attack,
        damage: 4
    );

    $result = $resolver->resolve(
        state: $state,
        playerDice: 1,
        npcDecision: $npcDecision
    );

    expect($result->player->hp)->toBe(26);
});

it('npc erra ataque', function () {

    $state = makeState();

    $resolver = new CombatResolver(
        new FakeDiceRoller(1)
    );

    $npcDecision = new NpcDecision(
        action: ActionType::Attack,
        damage: 4
    );

    $result = $resolver->resolve(
        state: $state,
        playerDice: 1,
        npcDecision: $npcDecision
    );

    expect($result->player->hp)->toBe(30);
});

it('encerra combate quando jogador morre', function () {

    $state = makeState(
        playerHp: 5
    );

    $resolver = new CombatResolver(
        new FakeDiceRoller(20)
    );

    $npcDecision = new NpcDecision(
        action: ActionType::Attack,
        damage: 10
    );

    $result = $resolver->resolve(
        state: $state,
        playerDice: 1,
        npcDecision: $npcDecision
    );

    expect($result->player->isAlive())->toBeFalse()
        ->and($result->combatEnded)->toBeTrue();
});
