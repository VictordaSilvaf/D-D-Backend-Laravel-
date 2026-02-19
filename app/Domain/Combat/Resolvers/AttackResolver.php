<?php

declare(strict_types=1);

namespace App\Domain\Combat\Resolvers;

use App\Domain\Combat\Character;
use App\Domain\Combat\CombatState;

final class AttackResolver
{
    public function resolve(
        Character $attacker,
        Character $defender,
        int $diceRoll,
        CombatState $combatState
    ): void {

        if ($diceRoll >= $defender->defense) {

            $damage = $attacker->damage;

            $defender->takeDamage($damage);

            $combatState->addLog(
                "Ataque acertou causando {$damage} de dano."
            );

            return;
        }

        $combatState->addLog('Ataque falhou.');
    }
}
