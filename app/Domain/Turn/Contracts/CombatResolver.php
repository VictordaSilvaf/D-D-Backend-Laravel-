<?php

declare(strict_types=1);

namespace App\Domain\Turn\Contracts;

use App\Domain\Combat\NpcDecision;
use App\Domain\Turn\TurnCombatResolverResult;

interface CombatResolver
{
    /**
     * @param  array<string, mixed>  $state
     * @param  NpcDecision  $npcDecision
     */
    public function resolve(array $state, int $dice, NpcDecision $npcDecision): TurnCombatResolverResult;
}
