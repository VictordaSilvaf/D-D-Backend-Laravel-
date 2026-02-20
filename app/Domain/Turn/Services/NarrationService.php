<?php

declare(strict_types=1);

namespace App\Domain\Turn\Services;

use App\Ai\Agents\DDMaster;
use App\Domain\Combat\NpcDecision;
use App\Domain\Turn\Contracts\NarrationService as NarrationServiceContract;
use Laravel\Ai\Responses\AgentResponse;

final class NarrationService implements NarrationServiceContract
{
    public function narrate(
        array $state,
        string $playerAction,
        array $dice,
        NpcDecision $npcDecision
    ): string {

        /** @var AgentResponse $response */
        $response = DDMaster::make()->prompt(
            json_encode([
                'scene_state' => $state,
                'player_action' => $playerAction,
                'player_dice_result' => $dice['player'],
                'npc_dice_result' => $dice['npc'],
                'npc_decision' => [
                    'action' => $npcDecision->action->value,
                    'damage' => $npcDecision->damage,
                ],
            ], JSON_THROW_ON_ERROR)
        );

        return (string) $response;
    }
}
