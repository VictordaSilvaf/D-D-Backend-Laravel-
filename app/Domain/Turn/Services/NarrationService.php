<?php

declare(strict_types=1);

namespace App\Domain\Turn\Services;

use App\Ai\Agents\DDMaster;
use App\Domain\Turn\Contracts\NarrationService as NarrationServiceContract;
use Laravel\Ai\Responses\AgentResponse;

final class NarrationService implements NarrationServiceContract
{
    public function narrate(
        array $state,
        string $playerAction,
        int $dice,
        array $npcDecision
    ): string {

        /** @var AgentResponse $response */
        $response = DDMaster::make()->prompt(
            json_encode([
                'scene_state' => $state,
                'player_action' => $playerAction,
                'dice_result' => $dice,
                'npc_decision' => $npcDecision,
            ], JSON_THROW_ON_ERROR)
        );

        return (string) $response;
    }
}
