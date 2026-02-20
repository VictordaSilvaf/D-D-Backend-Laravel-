<?php

declare(strict_types=1);

namespace App\Domain\Combat;

final class CombatTurnResult
{
    public function __construct(
        public readonly CombatState $combatState,
        public readonly ?int $npcRoll,
    ) {}
}
