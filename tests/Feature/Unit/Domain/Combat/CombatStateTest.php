<?php

declare(strict_types=1);

use App\Domain\Combat\Character;
use App\Domain\Combat\CombatState;
use App\Domain\Combat\Enums\ActionType;

it('adiciona log corretamente', function () {

    $state = new CombatState(
        player: new Character(30, 10, 5),
        enemy: new Character(20, 10, 4),
        playerAction: ActionType::Attack,
    );

    $state->addLog('Teste');

    expect($state->combatLog())->toHaveCount(1)
        ->and($state->combatLog()[0])->toBe('Teste');
});

it('marca combate como encerrado', function () {

    $state = new CombatState(
        player: new Character(30, 10, 5),
        enemy: new Character(20, 10, 4),
        playerAction: ActionType::Attack,
    );

    $state->endCombat();

    expect($state->combatEnded)->toBeTrue();
});
