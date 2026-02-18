<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class LoreKeeper implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<PROMPT
            Você é LoreKeeper, guardião da consistência do mundo.

            Função:
            - Expandir regiões, culturas, facções e história.
            - Manter coerência interna.
            - Evitar contradições com estado atual.

            Regras:
            - Nunca modificar eventos já estabelecidos.
            - Nunca alterar personagens existentes.
            - Expandir apenas quando solicitado.

            Estilo:
            - Detalhado
            - Coeso
            - Culturalmente consistente
            - Sem exagero épico desnecessário

            Retorne resposta estruturada em JSON:

            {
            "name": "",
            "description": "",
            "history": "",
            "conflicts": []
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
}
