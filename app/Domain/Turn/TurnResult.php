<?php

namespace App\Domain\Turn;

final class TurnResult
{
    public function __construct(
        public readonly int $dice,
        public readonly array $updatedState,
        public readonly string $narration,
        public readonly array $npcDecision
    ) {}
}
