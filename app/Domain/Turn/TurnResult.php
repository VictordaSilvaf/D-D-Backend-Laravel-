<?php

declare(strict_types=1);

namespace App\Domain\Turn;

use App\Domain\Combat\NpcDecision;

final class TurnResult
{
    /**
     * @param array<string, int> $dice
     * @param array<string, mixed> $updatedState
     * @param NpcDecision $npcDecision
     */
    public function __construct(
        public readonly array $dice,
        public readonly array $updatedState,
        public readonly string $narration,
        public readonly NpcDecision $npcDecision
    ) {}
}
