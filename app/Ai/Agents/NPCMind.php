<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class NPCMind implements Agent, HasTools, HasStructuredOutput, Conversational
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<PROMPT
        Você é NPCMind, responsável por decidir ações de NPCs de forma realista.

        Receberá:
        - Estado atual da cena
        - Personalidade do NPC
        - Objetivos
        - Medos
        - Condição física

        Função:
        Decidir a ação mais coerente com o perfil psicológico.

        Regras:
        - Nunca usar metagame.
        - Nunca saber informações que o NPC não sabe.
        - Nunca alterar mecânicas.
        - Apenas decidir intenção.

        Responda em JSON:

        {
          "intention": "",
          "emotional_state": "",
          "strategy": ""
        }
        PROMPT;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'value' => $schema->string()->required(),
        ];
    }
}
