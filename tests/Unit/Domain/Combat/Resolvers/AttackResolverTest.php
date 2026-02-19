<?php

declare(strict_types=1);

use App\Domain\Combat\Character;
use App\Domain\Combat\CombatState;
use App\Domain\Combat\Enums\ActionType;
use App\Domain\Combat\Resolvers\AttackResolver;

it('aplica dano quando ataque acerta', function () {

    $state = makeState();
    $resolver = new AttackResolver();

    $resolver->resolve(
        attacker: $state->player,
        defender: $state->enemy,
        diceRoll: 15,
        combatState: $state
    );

    expect($state->enemy->hp)->toBe(15);
});


it('nao aplica dano quando ataque falha', function () {

    $state = makeState();
    $resolver = new AttackResolver();

    $resolver->resolve(
        attacker: $state->player,
        defender: $state->enemy,
        diceRoll: 5,
        combatState: $state
    );

    expect($state->enemy->hp)->toBe(20);
});

it('registra log de sucesso', function () {

    $state = makeState();
    $resolver = new AttackResolver();

    $resolver->resolve(
        attacker: $state->player,
        defender: $state->enemy,
        diceRoll: 20,
        combatState: $state
    );

    expect($state->combatLog())
        ->toContain('Ataque acertou causando 5 de dano.');
});

it('registra log de falha', function () {

    $state = makeState();
    $resolver = new AttackResolver();

    $resolver->resolve(
        attacker: $state->player,
        defender: $state->enemy,
        diceRoll: 1,
        combatState: $state
    );

    expect($state->combatLog())
        ->toContain('Ataque falhou.');
});

it('hp nunca fica negativo mesmo com dano alto', function () {

    $state = new CombatState(
        player: new Character(30, 10, 100),
        enemy: new Character(10, 10, 4),
        playerAction: ActionType::Attack,
    );

    $resolver = new AttackResolver();

    $resolver->resolve(
        attacker: $state->player,
        defender: $state->enemy,
        diceRoll: 20,
        combatState: $state
    );

    expect($state->enemy->hp)->toBe(0);
});
