<?php

declare(strict_types=1);

namespace App\Domain\Combat;

use App\Domain\Combat\Contracts\DiceRoller;
use App\Domain\Combat\Enums\ActionType;

final class CombatResolver
{
    public function __construct(
        private readonly DiceRoller $diceRoller
    ) {}

    public function resolve(
        CombatState $state,
        int $playerDice,
        NpcDecision $npcDecision
    ): CombatState {

        if ($state->playerAction === ActionType::Attack) {
            $this->resolvePlayerAttack($state, $playerDice);
        }

        if (! $state->enemy->isAlive()) {
            $state->addLog('Inimigo derrotado.');
            $state->endCombat();

            return $state;
        }

        if ($npcDecision->action === ActionType::Attack) {
            $this->resolveNpcAttack($state, $npcDecision);
        }

        if (! $state->player->isAlive()) {
            $state->addLog('Jogador derrotado.');
            $state->endCombat();
        }

        return $state;
    }

    private function resolvePlayerAttack(
        CombatState $state,
        int $dice
    ): void {
        if ($dice >= $state->enemy->defense) {
            $damage = $state->player->damage;
            $state->enemy->takeDamage($damage);

            $state->addLog("Ataque acertou causando {$damage} de dano.");

            return;
        }

        $state->addLog('Ataque falhou.');
    }

    private function resolveNpcAttack(
        CombatState $state,
        NpcDecision $decision
    ): void {
        $roll = $this->diceRoller->roll();

        if ($roll >= $state->player->defense) {
            $state->player->takeDamage($decision->damage);

            $state->addLog("Inimigo atacou causando {$decision->damage} de dano.");

            return;
        }

        $state->addLog('Inimigo tentou atacar, mas falhou.');
    }
}
