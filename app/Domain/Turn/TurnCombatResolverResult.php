<?php

declare(strict_types=1);

namespace App\Domain\Turn;

final class TurnCombatResolverResult
{
    public function __construct(
        public readonly array $state,
        public readonly ?int $npcRoll,
    ) {}
}
