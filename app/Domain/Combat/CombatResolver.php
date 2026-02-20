<?php

declare(strict_types=1);

namespace App\Domain\Combat;

use App\Domain\Combat\CombatTurnResult;
use App\Domain\Combat\Contracts\CombatResolver as CombatResolverContract;
use App\Domain\Combat\Contracts\DiceRoller;
use App\Domain\Combat\Enums\ActionType;
use App\Domain\Combat\Resolvers\AttackResolver;

final class CombatResolver implements CombatResolverContract
{
    public function __construct(
        private readonly DiceRoller $diceRoller,
        private readonly AttackResolver $attackResolver
    ) {}

    public function resolve(
        CombatState $state,
        int $playerDice,
        NpcDecision $npcDecision
    ): CombatTurnResult {
        if ($state->playerAction === ActionType::Attack) {
            $this->attackResolver->resolve(
                attacker: $state->player,
                defender: $state->enemy,
                diceRoll: $playerDice,
                combatState: $state
            );
        }

        if ($state->playerAction === ActionType::Flee) {
            $state->addLog('Jogador fugiu do combate.');
            $state->endCombat();

            return new CombatTurnResult($state, null);
        }

        if (! $state->enemy->isAlive()) {
            $state->addLog('Inimigo derrotado.');
            $state->endCombat();

            return new CombatTurnResult($state, null);
        }


        $npcRoll = null;
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

        return new CombatTurnResult($state, $npcRoll);
    }
}
