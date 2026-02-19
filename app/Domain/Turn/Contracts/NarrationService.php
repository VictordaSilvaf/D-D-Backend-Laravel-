<?php

declare(strict_types=1);

namespace App\Domain\Turn\Contracts;

interface NarrationService
{
    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $npcDecision
     */
    public function narrate(array $state, string $playerAction, int $dice, array $npcDecision): string;
}
