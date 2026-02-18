<?php

namespace App\Domain\Turn;

use App\Ai\Agents\DDMaster;
use App\Ai\Agents\NPCMind;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\StructuredAgentResponse;

final class TurnEngine
{
    public function __construct(
        private DiceRoller $diceRoller,
        private CombatResolver $combatResolver,
    ) {}

    public function process(array $state, string $playerAction): TurnResult
    {
        $dice = $this->diceRoller->d20();

        /*
         |------------------------------------------------------------
         | 1. Decisão dos NPCs (Structured Output)
         |------------------------------------------------------------
         */

        /** @var StructuredAgentResponse $npcResponse */
        $npcResponse = NPCMind::make()->prompt(
            $this->buildNpcPrompt($state, $playerAction, $dice)
        );

        $npcDecision = $npcResponse->toArray();

        /*
         |------------------------------------------------------------
         | 2. Aplicar regras de combate
         |------------------------------------------------------------
         */

        $updatedState = $this->combatResolver->resolve(
            state: $state,
            dice: $dice,
            npcDecision: $npcDecision
        );

        /*
         |------------------------------------------------------------
         | 3. Gerar narração
         |------------------------------------------------------------
         */

        /** @var AgentResponse $narrationResponse */
        $narrationResponse = DDMaster::make()->prompt(
            $this->buildNarrationPrompt(
                $updatedState,
                $playerAction,
                $dice,
                $npcDecision
            )
        );

        return new TurnResult(
            dice: $dice,
            updatedState: $updatedState,
            narration: (string) $narrationResponse,
            npcDecision: $npcDecision
        );
    }

    /*
     |--------------------------------------------------------------------------
     | Prompt Builders
     |--------------------------------------------------------------------------
     | DRY aplicado: separação da construção de prompt
     */

    private function buildNpcPrompt(
        array $state,
        string $playerAction,
        int $dice
    ): string {
        return json_encode([
            'scene_state' => $state,
            'player_action' => $playerAction,
            'dice_result' => $dice,
        ], JSON_THROW_ON_ERROR);
    }

    private function buildNarrationPrompt(
        array $state,
        string $playerAction,
        int $dice,
        array $npcDecision
    ): string {
        return json_encode([
            'scene_state' => $state,
            'player_action' => $playerAction,
            'dice_result' => $dice,
            'npc_decision' => $npcDecision,
        ], JSON_THROW_ON_ERROR);
    }
}
