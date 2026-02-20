<?php

declare(strict_types=1);

namespace App\Domain\Turn;

use App\Domain\Turn\Contracts\CombatResolver as CombatResolverContract;
use App\Domain\Turn\Contracts\DiceRoller as DiceRollerContract;
use App\Domain\Turn\Contracts\NarrationService as NarrationServiceContract;
use App\Domain\Turn\Contracts\NpcDecisionService as NpcDecisionServiceContract;

final class TurnEngine
{
    public function __construct(
        private DiceRollerContract $diceRoller,
        private CombatResolverContract $combatResolver,
        private NpcDecisionServiceContract $npcDecisionService,
        private NarrationServiceContract $narrationService,
    ) {}

    public function process(array $state, string $playerAction): TurnResult
    {
        $dice = $this->diceRoller->d20();

        $enrichedState = [
            ...$state,
            'player_action' => $playerAction,
        ];
        $npcDecision = $this->npcDecisionService->decide(
            $enrichedState,
            $playerAction,
            $dice
        );

        $resolvedTurn = $this->combatResolver->resolve(
            state: $enrichedState,
            dice: $dice,
            npcDecision: $npcDecision
        );

        $narration = $this->narrationService->narrate(
            $resolvedTurn->state,
            $playerAction,
            [
                'player' => $dice,
                'npc' => $resolvedTurn->npcRoll,
            ],
            $npcDecision
        );

        return new TurnResult(
            dice: [
                'player' => $dice,
                'npc' => $resolvedTurn->npcRoll,
            ],
            updatedState: $resolvedTurn->state,
            narration: $narration,
            npcDecision: $npcDecision
        );
    }
}
