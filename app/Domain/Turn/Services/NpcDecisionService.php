<?php

declare(strict_types=1);

namespace App\Domain\Turn\Services;  // ajuste o namespace se estiver errado

use App\Ai\Agents\NPCMind;
use App\Domain\Combat\NpcDecision;
use App\Domain\Turn\Contracts\NpcDecisionService as NpcDecisionServiceContract;

final class NpcDecisionService implements NpcDecisionServiceContract
{
    public function decide(
        array $state,
        string $playerAction,
        int $dice
    ): NpcDecision {   // ← mude aqui o tipo de retorno

        $response = NPCMind::make()->prompt(
            json_encode([
                'scene_state' => $state,
                'player_action' => $playerAction,
                'dice_result' => $dice,
            ], JSON_THROW_ON_ERROR)
        );
        $raw = $response->toArray();

        return NpcDecision::fromAgentResponse($raw);
    }
}
