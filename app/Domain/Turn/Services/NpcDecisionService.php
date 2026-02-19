<?php

declare(strict_types=1);

namespace App\Domain\Turn\Services;

use App\Ai\Agents\NPCMind;
use App\Domain\Turn\Contracts\NpcDecisionService as NpcDecisionServiceContract;
use Laravel\Ai\Responses\StructuredAgentResponse;

final class NpcDecisionService implements NpcDecisionServiceContract
{
    public function decide(
        array $state,
        string $playerAction,
        int $dice
    ): array {

        /** @var StructuredAgentResponse $response */
        $response = NPCMind::make()->prompt(
            json_encode([
                'scene_state' => $state,
                'player_action' => $playerAction,
                'dice_result' => $dice,
            ], JSON_THROW_ON_ERROR)
        );

        return $response->toArray();
    }
}
