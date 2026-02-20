<?php

declare(strict_types=1);

namespace App\Domain\Combat\Contracts;

use App\Domain\Combat\CombatState;
use App\Domain\Combat\CombatTurnResult;
use App\Domain\Combat\NpcDecision;

interface CombatResolver
{
    public function resolve(
        CombatState $state,
        int $playerDice,
        NpcDecision $npcDecision
    ): CombatTurnResult;
}