<?php

declare(strict_types=1);

namespace App\Domain\Turn\Contracts;

interface NpcDecisionService
{
    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function decide(array $state, string $playerAction, int $dice): array;
}
