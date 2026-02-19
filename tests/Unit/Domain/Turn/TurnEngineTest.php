<?php

declare(strict_types=1);

use App\Domain\Turn\Contracts\CombatResolver as CombatResolverContract;
use App\Domain\Turn\Contracts\DiceRoller as DiceRollerContract;
use App\Domain\Turn\Contracts\NarrationService as NarrationServiceContract;
use App\Domain\Turn\Contracts\NpcDecisionService as NpcDecisionServiceContract;
use App\Domain\Turn\TurnEngine;
use App\Domain\Turn\TurnResult;

it('processa um turno corretamente', function () {

    $initialState = [
        'player_hp' => 100,
        'npc_hp' => 80,
    ];

    $playerAction = 'attack';

    $dice = 18;

    $npcDecision = [
        'action' => 'defend',
    ];

    $updatedState = [
        'player_hp' => 100,
        'npc_hp' => 70,
    ];

    $narration = 'Você atacou e o NPC tentou se defender.';

    /*
     |--------------------------------------------------------------------------
     | Mocks
     |--------------------------------------------------------------------------
     */

    $diceRoller = mock(DiceRollerContract::class);
    $combatResolver = mock(CombatResolverContract::class);
    $npcDecisionService = mock(NpcDecisionServiceContract::class);
    $narrationService = mock(NarrationServiceContract::class);

    $enrichedState = [
        'player_hp' => 100,
        'npc_hp' => 80,
        'player_action' => $playerAction,
    ];

    $diceRoller
        ->shouldReceive('d20')
        ->once()
        ->andReturn($dice);

    $npcDecisionService
        ->shouldReceive('decide')
        ->once()
        ->with($enrichedState, $playerAction, $dice)
        ->andReturn($npcDecision);

    $combatResolver
        ->shouldReceive('resolve')
        ->once()
        ->with($enrichedState, $dice, $npcDecision)
        ->andReturn($updatedState);

    $narrationService
        ->shouldReceive('narrate')
        ->once()
        ->with(
            $updatedState,
            $playerAction,
            $dice,
            $npcDecision
        )
        ->andReturn($narration);

    /*
     |--------------------------------------------------------------------------
     | Act
     |--------------------------------------------------------------------------
     */

    $engine = new TurnEngine(
        $diceRoller,
        $combatResolver,
        $npcDecisionService,
        $narrationService
    );

    $result = $engine->process($initialState, $playerAction);

    /*
     |--------------------------------------------------------------------------
     | Assert
     |--------------------------------------------------------------------------
     */

    expect($result)
        ->toBeInstanceOf(TurnResult::class);

    expect($result->dice)->toBe($dice);
    expect($result->updatedState)->toBe($updatedState);
    expect($result->narration)->toBe($narration);
    expect($result->npcDecision)->toBe($npcDecision);
});
