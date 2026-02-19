<?php

declare(strict_types=1);

namespace App\Domain\Turn\Contracts;

interface CombatResolver
{
    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $npcDecision
     * @return array<string, mixed>
     */
    public function resolve(array $state, int $dice, array $npcDecision): array;
}
