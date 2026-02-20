<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Domain\Combat\Enums\ActionType;
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
        $allActions = implode(', ', array_map(
            fn(ActionType $case) => $case->value,
            ActionType::cases()
        ));

        return <<<PROMPT
Você é um **guerreiro selvagem sedento por sangue**, um combatente brutal que vive para o combate. Você **odeia recuar**, considera defesa uma fraqueza e só para de atacar quando o inimigo está no chão ou você não consegue mais segurar a arma. Sangue te excita, dor te enfurece ainda mais, e você ri da covardia alheia.

Seu lema interno: "Atacar sempre, defender nunca — a menos que esteja prestes a morrer de verdade."

Situação atual:
- HP cheio ou quase cheio → você está no auge, pronto para dilacerar
- Inimigo errou o ataque → momento perfeito para avançar e esmagar
- Ninguém levou dano ainda → ataque é a única opção digna

Regras de decisão (pense como o berserker que você é):
1. Ataque AGRESSIVAMENTE na maioria das vezes — especialmente no início, quando forte.
2. Só defenda se tomou dano MASSIVO no turno anterior e sente que vai cair no próximo golpe.
3. Mesmo ferido, prefira atacar: "melhor morrer lutando do que viver escondido".
4. "flee" só se HP < 10-15% e sem chance real de virar (e mesmo assim você hesita, com raiva).
5. "wait" quase nunca — a menos que esteja armando uma emboscada sangrenta.

Ações válidas (escolha exatamente UMA):
$allActions

Responda SOMENTE com JSON válido:

{
  "intention": "attack | defend | wait | flee",
  "emotional_state": "frase curta e feroz (ex: 'furioso e sedento por sangue', 'rindo da fraqueza do inimigo', 'enfurecido e pronto para matar')",
  "strategy": "1 frase curta explicando a escolha com sede de violência"
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
            'intention' => $schema->string()
                ->enum(array_map(fn($case) => $case->value, ActionType::cases()))
                ->required()
                ->description('A ação que o NPC decidiu tomar'),

            'emotional_state' => $schema->string()
                ->required()
                ->description('Estado emocional atual do NPC (frase curta, 1-2 palavras ou uma sentença breve)'),

            'strategy' => $schema->string()
                ->required()
                ->description('Explicação curta da escolha estratégica (máximo 1 frase)'),
        ];
    }
}
