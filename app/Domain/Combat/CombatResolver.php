<?php

declare(strict_types=1);

namespace App\Domain\Combat;

use App\Domain\Combat\Contracts\DiceRoller;
use App\Domain\Combat\Enums\ActionType;
use App\Domain\Combat\Resolvers\AttackResolver;

final class CombatResolver
{
    public function __construct(
        private readonly DiceRoller $diceRoller,
        private readonly AttackResolver $attackResolver
    ) {}

    public function resolve(
        CombatState $state,
        int $playerDice,
        NpcDecision $npcDecision
    ): CombatState {

        if ($state->playerAction === ActionType::Attack) {
            $this->attackResolver->resolve(
                attacker: $state->player,
                defender: $state->enemy,
                diceRoll: $playerDice,
                combatState: $state
            );
        }

        if (! $state->enemy->isAlive()) {
            $state->addLog('Inimigo derrotado.');
            $state->endCombat();

            return $state;
        }

        if ($npcDecision->action === ActionType::Attack) {

            $npcRoll = $this->diceRoller->roll();

            $this->attackResolver->resolve(
                attacker: $state->enemy,
                defender: $state->player,
                diceRoll: $npcRoll,
                combatState: $state
            );
        }

        if (! $state->player->isAlive()) {
            $state->addLog('Jogador derrotado.');
            $state->endCombat();
        }

        return $state;
    }
}
