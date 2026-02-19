<?php

declare(strict_types=1);

namespace App\Domain\Turn;

use App\Domain\Combat\Contracts\CombatResolver as CombatResolverContract;
use App\Domain\Turn\Contracts\CombatResolver as TurnCombatResolverContract;

final class CombatResolverAdapter implements TurnCombatResolverContract
{
    public function __construct(
        private readonly CombatResolverContract $combatResolver,
        private readonly CombatStateArrayMapper $mapper,
    ) {}

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $npcDecision
     * @return array<string, mixed>
     */
    public function resolve(array $state, int $dice, array $npcDecision): array
    {
        $combatState = $this->mapper->toCombatState($state);
        $npcDecisionObj = $this->mapper->npcDecisionFromArray($npcDecision);

        $resolvedState = $this->combatResolver->resolve(
            $combatState,
            $dice,
            $npcDecisionObj
        );

        return $this->mapper->toArray($resolvedState);
    }
}
