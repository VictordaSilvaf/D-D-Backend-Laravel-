<?php

declare(strict_types=1);

use App\Domain\Combat\CombatResolver;
use App\Domain\Combat\CombatTurnResult;
use App\Domain\Combat\Enums\ActionType;
use App\Domain\Combat\NpcDecision;
use App\Domain\Combat\Resolvers\AttackResolver;
use Tests\Support\FakeDiceRoller;

/**
 * Orquestração e fim de combate: ordem das fases, early return quando alguém morre.
 */
describe('CombatResolver', function () {

    it('aplica ataque do jogador quando playerAction é Attack e reduz HP do inimigo', function () {

        $state = makeState();

        $resolver = new CombatResolver(
            new FakeDiceRoller(1),
            new AttackResolver
        );

        $npcDecision = new NpcDecision(
            action: ActionType::Attack,
            damage: 4
        );

        $result = $resolver->resolve(
            state: $state,
            playerDice: 15,
            npcDecision: $npcDecision
        );

        expect($result->combatState->enemy->hp)->toBe(15);
    });

    it('não aplica ataque do jogador quando playerAction não é Attack', function () {
        $state = makeState();
        $state->playerAction = ActionType::Defend;

        $resolver = new CombatResolver(
            new FakeDiceRoller(1),
            new AttackResolver
        );

        $npcDecision = new NpcDecision(
            action: ActionType::Attack,
            damage: 4
        );

        $result = $resolver->resolve(
            state: $state,
            playerDice: 15,
            npcDecision: $npcDecision
        );

        expect($result->combatState->enemy->hp)->toBe(20);
    });

    it('encerra combate quando inimigo morre e não rola NPC', function () {

        $state = makeState(
            playerHp: 30,
            enemyHp: 10,
            playerDamage: 50
        );

        $resolver = new CombatResolver(
            new FakeDiceRoller(1),
            new AttackResolver
        );

        $npcDecision = new NpcDecision(
            action: ActionType::Attack,
            damage: 4
        );

        $result = $resolver->resolve(
            state: $state,
            playerDice: 20,
            npcDecision: $npcDecision
        );

        expect($result->combatState->enemy->isAlive())->toBeFalse()
            ->and($result->combatState->combatEnded)->toBeTrue();
    });

    it('npc acerta ataque', function () {

        $state = makeState();

        $resolver = new CombatResolver(
            new FakeDiceRoller(20),
            new AttackResolver
        );

        $npcDecision = new NpcDecision(
            action: ActionType::Attack,
            damage: 4
        );

        $result = $resolver->resolve(
            state: $state,
            playerDice: 1,
            npcDecision: $npcDecision
        );

        expect($result->combatState->player->hp)->toBe(26);
    });

    it('npc erra ataque', function () {

        $state = makeState();

        $resolver = new CombatResolver(
            new FakeDiceRoller(1),
            new AttackResolver
        );

        $npcDecision = new NpcDecision(
            action: ActionType::Attack,
            damage: 4
        );

        $result = $resolver->resolve(
            state: $state,
            playerDice: 1,
            npcDecision: $npcDecision
        );

        expect($result->combatState->player->hp)->toBe(30);
    });

    it('não aplica ataque do NPC quando npcDecision action não é Attack', function () {
        $state = makeState();

        $resolver = new CombatResolver(
            new FakeDiceRoller(20),
            new AttackResolver
        );

        $npcDecision = new NpcDecision(
            action: ActionType::Defend,
            damage: 4
        );

        $result = $resolver->resolve(
            state: $state,
            playerDice: 1,
            npcDecision: $npcDecision
        );

        expect($result->combatState->player->hp)->toBe(30);
    });

    it('encerra combate quando jogador morre', function () {

        $state = makeState(
            playerHp: 5,
            enemyDamage: 10
        );

        $resolver = new CombatResolver(
            new FakeDiceRoller(20),
            new AttackResolver
        );

        $npcDecision = new NpcDecision(
            action: ActionType::Attack,
            damage: 10
        );

        $result = $resolver->resolve(
            state: $state,
            playerDice: 1,
            npcDecision: $npcDecision
        );

        expect($result->combatState->player->isAlive())->toBeFalse()
            ->and($result->combatState->combatEnded)->toBeTrue();
    });

});
