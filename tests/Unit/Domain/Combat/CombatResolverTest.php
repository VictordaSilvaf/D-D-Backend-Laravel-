<?php

declare(strict_types=1);

use App\Domain\Combat\Character;
use App\Domain\Combat\CombatResolver;
use App\Domain\Combat\CombatState;
use App\Domain\Combat\Enums\ActionType;
use App\Domain\Combat\NpcDecision;
use Tests\Support\FakeDiceRoller;

function makeDefaultState(): CombatState
{
    return new CombatState(
        player: new Character(hp: 30, defense: 10, damage: 5),
        enemy: new Character(hp: 20, defense: 10, damage: 4),
        playerAction: ActionType::Attack,
    );
}

it('player acerta ataque', function () {

    $state = makeDefaultState();

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
