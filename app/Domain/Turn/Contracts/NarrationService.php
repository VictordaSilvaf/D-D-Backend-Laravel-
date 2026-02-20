<?php

declare(strict_types=1);

namespace App\Domain\Turn\Contracts;

use App\Domain\Combat\NpcDecision;

interface NarrationService
{
    /**
     * @param  array<string, mixed>  $state
     * @param  NpcDecision  $npcDecision
     */
    public function narrate(array $state, string $playerAction, array $dice, NpcDecision $npcDecision): string;
}
