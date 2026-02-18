<?php

declare(strict_types=1);

namespace App\Domain\Combat;

final class CombatResolver
{
    public function resolve(
        CombatState $state,
        int $dice,
        NpcDecision $npcDecision
    ): CombatState {

        if ($state->playerAction === ActionType::Attack) {
            $this->resolvePlayerAttack($state, $dice);
        }

        if (!$state->enemy->isAlive()) {
            $state->addLog('Inimigo derrotado.');
            $state->endCombat();
            return $state;
        }

        if ($npcDecision->action === ActionType::Attack) {
            $this->resolveNpcAttack($state, $npcDecision);
        }

        if (!$state->player->isAlive()) {
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
        $roll = random_int(1, 20);

        if ($roll >= $state->player->defense) {
            $state->player->takeDamage($decision->damage);

            $state->addLog("Inimigo atacou causando {$decision->damage} de dano.");
            return;
        }

        $state->addLog('Inimigo tentou atacar, mas falhou.');
    }
}
