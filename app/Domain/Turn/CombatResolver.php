<?php

declare(strict_types=1);

namespace App\Domain\Turn;

final class CombatResolver
{
    public function resolve(
        array $state,
        int $dice,
        array $npcDecision
    ): array {
        $state['combat_log'] ??= [];

        /*
        |--------------------------------------------------------------------------
        | 1. Player Attack Phase
        |--------------------------------------------------------------------------
        */

        if ($this->isAttack($state['player_action'] ?? null)) {
            $attackResult = $this->resolvePlayerAttack($state, $dice);
            $state = $attackResult['state'];
            $state['combat_log'][] = $attackResult['log'];
        }

        /*
        |--------------------------------------------------------------------------
        | 2. NPC Phase
        |--------------------------------------------------------------------------
        */

        if (!$this->isEnemyAlive($state)) {
            $state['combat_log'][] = 'Inimigo derrotado.';
            $state['combat_ended'] = true;
            return $state;
        }

        if (($npcDecision['action'] ?? null) === 'attack') {
            $npcResult = $this->resolveNpcAttack($state, $npcDecision);
            $state = $npcResult['state'];
            $state['combat_log'][] = $npcResult['log'];
        }

        if (!$this->isPlayerAlive($state)) {
            $state['combat_log'][] = 'Jogador derrotado.';
            $state['combat_ended'] = true;
        }

        return $state;
    }

    /*
    |--------------------------------------------------------------------------
    | Player Attack
    |--------------------------------------------------------------------------
    */

    private function resolvePlayerAttack(array $state, int $dice): array
    {
        $enemyDefense = $state['enemy']['defense'] ?? 12;
        $playerDamage = $state['player']['damage'] ?? 5;

        if ($dice >= $enemyDefense) {
            $state['enemy']['hp'] -= $playerDamage;
            $state['enemy']['hp'] = max(0, $state['enemy']['hp']);

            return [
                'state' => $state,
                'log' => "Ataque acertou causando {$playerDamage} de dano.",
            ];
        }

        return [
            'state' => $state,
            'log' => 'Ataque falhou.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | NPC Attack
    |--------------------------------------------------------------------------
    */

    private function resolveNpcAttack(array $state, array $npcDecision): array
    {
        $npcDamage = $npcDecision['damage'] ?? 3;
        $playerDefense = $state['player']['defense'] ?? 12;

        $roll = rand(1, 20);

        if ($roll >= $playerDefense) {
            $state['player']['hp'] -= $npcDamage;
            $state['player']['hp'] = max(0, $state['player']['hp']);

            return [
                'state' => $state,
                'log' => "Inimigo atacou causando {$npcDamage} de dano.",
            ];
        }

        return [
            'state' => $state,
            'log' => 'Inimigo tentou atacar, mas falhou.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function isAttack(?string $action): bool
    {
        return $action === 'attack';
    }

    private function isEnemyAlive(array $state): bool
    {
        return ($state['enemy']['hp'] ?? 0) > 0;
    }

    private function isPlayerAlive(array $state): bool
    {
        return ($state['player']['hp'] ?? 0) > 0;
    }
}
