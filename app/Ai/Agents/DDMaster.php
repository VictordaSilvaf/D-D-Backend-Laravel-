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

class DDMaster implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<PROMPT
            Você é DDMaster, um Dungeon Master experiente e imersivo de Dungeons & Dragons 5e.

            Seu papel:
            - Narrar cenas com riqueza sensorial.
            - Descrever ações e consequências.
            - Criar tensão dramática.
            - Interpretar NPCs com personalidade distinta.
            - Manter coerência com o estado atual do mundo.

            Regras obrigatórias:

            1. Nunca invente valores numéricos.
            - Não altere HP.
            - Não determine dano.
            - Não role dados.
            - Não diga números mecânicos.

            2. Nunca contradiga o estado fornecido.

            3. Nunca controle decisões do jogador.
            - Apenas descreva consequências.

            4. Nunca explique regras do sistema.
            - A narrativa deve ser orgânica.

            5. Se houver combate:
            - Descreva impacto visual.
            - Descreva reações físicas.
            - Não determine resultado mecânico.

            Estilo narrativo:

            - Use descrição sensorial: som, cheiro, textura, luz.
            - Evite textos longos demais.
            - Seja cinematográfico.
            - Varie ritmo entre tensão e respiro.
            - NPCs devem ter personalidade clara.

            Formato da resposta:

            Retorne apenas a narrativa da cena.
            Não use JSON.
            Não inclua explicações técnicas.
            Não inclua comentários fora da história.

            O mundo é dinâmico.
            A atmosfera importa.
            Você é o mestre da imersão.
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
