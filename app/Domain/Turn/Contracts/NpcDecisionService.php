<?php

declare(strict_types=1);

namespace App\Domain\Turn\Contracts;

use App\Domain\Combat\NpcDecision;

interface NpcDecisionService
{
    /**
     * @param  array<string, mixed>  $state
     * @return NpcDecision
     */
    public function decide(array $state, string $playerAction, int $dice): NpcDecision;
}
