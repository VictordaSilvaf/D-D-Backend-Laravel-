<?php

declare(strict_types=1);

namespace App\Domain\Combat;

use App\Domain\Combat\Enums\ActionType;

final class CombatState
{
    /** @var list<string> */
    private array $combatLog = [];

    public bool $combatEnded = false;

    public function __construct(
        public Character $player,
        public Character $enemy,
        public ActionType $playerAction,
    ) {}

    public function addLog(string $message): void
    {
        $this->combatLog[] = $message;
    }

    /** @return list<string> */
    public function combatLog(): array
    {
        return $this->combatLog;
    }

    public function endCombat(): void
    {
        $this->combatEnded = true;
    }
}
