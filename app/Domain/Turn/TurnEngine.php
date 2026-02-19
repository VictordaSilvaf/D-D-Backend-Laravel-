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

        $updatedState = $this->combatResolver->resolve(
            state: $enrichedState,
            dice: $dice,
            npcDecision: $npcDecision
        );

        $narration = $this->narrationService->narrate(
            $updatedState,
            $playerAction,
            $dice,
            $npcDecision
        );

        return new TurnResult(
            dice: $dice,
            updatedState: $updatedState,
            narration: $narration,
            npcDecision: $npcDecision
        );
    }
}
