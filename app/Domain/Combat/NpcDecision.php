<?php

declare(strict_types=1);

namespace App\Domain\Combat;

final class NpcDecision
{
    public function __construct(
        public readonly ActionType $action,
        public readonly int $damage = 0,
    ) {}
}
