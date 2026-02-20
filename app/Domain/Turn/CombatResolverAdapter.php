<?php

declare(strict_types=1);

namespace App\Domain\Turn;

use App\Domain\Combat\Contracts\CombatResolver as CombatResolverContract;
use App\Domain\Combat\NpcDecision;
use App\Domain\Turn\Contracts\CombatResolver as TurnCombatResolverContract;
use App\Domain\Turn\TurnCombatResolverResult;

final class CombatResolverAdapter implements TurnCombatResolverContract
{
    public function __construct(
        private readonly CombatResolverContract $combatResolver,
        private readonly CombatStateArrayMapper $mapper,
    ) {}

    /**
     * @param  array<string, mixed>  $state
     * @param  NpcDecision  $npcDecision
     */
    public function resolve(array $state, int $dice, NpcDecision $npcDecision): TurnCombatResolverResult
    {
        $combatState = $this->mapper->toCombatState($state);

        $resolvedState = $this->combatResolver->resolve(
            $combatState,
            $dice,
            $npcDecision
        );

        return new TurnCombatResolverResult(
            state: $this->mapper->toArray($resolvedState->combatState),
            npcRoll: $resolvedState->npcRoll,
        );
    }
}
