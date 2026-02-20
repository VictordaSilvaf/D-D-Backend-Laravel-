<?php

declare(strict_types=1);

namespace App\Domain\Turn;

use App\Domain\Combat\Character;
use App\Domain\Combat\CombatState;
use App\Domain\Combat\Enums\ActionType;
use App\Domain\Combat\NpcDecision;

/**
 * Maps between Turn layer array state and Combat domain objects.
 * Array contract: state['player'] and state['enemy'] with 'hp', 'defense', 'damage'; state['player_action'] string.
 */
final class CombatStateArrayMapper
{
    /**
     * @param  array<string, mixed>  $state  Nested: 'player'/'enemy' with hp, defense, damage; or flat: player_hp, npc_hp (enemy). 'player_action' required.
     */
    public function toCombatState(array $state): CombatState
    {
        $player = $this->characterFromArray($this->normalizePlayerData($state));
        $enemy = $this->characterFromArray($this->normalizeEnemyData($state));
        $playerAction = $this->actionTypeFromString((string) ($state['player_action'] ?? 'wait'));

        return new CombatState(
            player: $player,
            enemy: $enemy,
            playerAction: $playerAction,
        );
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function normalizePlayerData(array $state): array
    {
        if (isset($state['player']) && is_array($state['player'])) {
            return $state['player'];
        }

        return [
            'hp' => $state['player_hp'] ?? 0,
            'defense' => $state['player_defense'] ?? 10,
            'damage' => $state['player_damage'] ?? 5,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function normalizeEnemyData(array $state): array
    {
        if (isset($state['enemy']) && is_array($state['enemy'])) {
            return $state['enemy'];
        }

        return [
            'hp' => $state['npc_hp'] ?? $state['enemy_hp'] ?? 0,
            'defense' => $state['npc_defense'] ?? $state['enemy_defense'] ?? 10,
            'damage' => $state['npc_damage'] ?? $state['enemy_damage'] ?? 5,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(CombatState $combatState): array
    {
        return [
            'player' => [
                'hp' => $combatState->player->hp,
                'defense' => $combatState->player->defense,
                'damage' => $combatState->player->damage,
            ],
            'enemy' => [
                'hp' => $combatState->enemy->hp,
                'defense' => $combatState->enemy->defense,
                'damage' => $combatState->enemy->damage,
            ],
            'player_action' => $combatState->playerAction->value,
            'combat_log' => $combatState->combatLog(),
            'combat_ended' => $combatState->combatEnded,
        ];
    }

    /**
     * @param  NpcDecision  $npcDecision
     * @return NpcDecision
     */
    public function npcDecisionFromArray($npcDecision): NpcDecision
    {
        $action = $this->actionTypeFromString((string) ($npcDecision['action'] ?? 'wait'));
        $damage = (int) ($npcDecision['damage'] ?? 0);

        return new NpcDecision(action: $action, damage: $damage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function characterFromArray(array $data): Character
    {
        $hp = (int) ($data['hp'] ?? 0);
        $defense = (int) ($data['defense'] ?? 10);
        $damage = (int) ($data['damage'] ?? 5);

        return new Character(hp: $hp, defense: $defense, damage: $damage);
    }

    private function actionTypeFromString(string $value): ActionType
    {
        return ActionType::tryFrom($value) ?? ActionType::Wait;
    }
}
