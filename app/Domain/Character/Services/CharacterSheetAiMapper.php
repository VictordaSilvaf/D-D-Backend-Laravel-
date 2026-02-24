<?php

declare(strict_types=1);

namespace App\Domain\Character\Services;

use App\Domain\Character\AI\CharacterSheetAiSchema;
use App\Domain\Character\Support\WeaponNormalizer;
use Illuminate\Support\Arr;
use Laravel\Ai\Ai;
use Laravel\Ai\Responses\StructuredAgentResponse;

final class CharacterSheetAiMapper
{
    public function map(string $pdfText): array
    {
        /** @var StructuredAgentResponse $response */
        $response = Ai::agent()
            ->system($this->systemPrompt())
            ->user($pdfText)
            ->structured(CharacterSheetAiSchema::class)
            ->send();

        $state = $response->toArray()['state'] ?? [];

        return $this->normalize($state);
    }

    /**
     * Normaliza valores extraídos pela IA
     */
    private function normalize(array $state): array
    {
        // Normaliza arma
        if (isset($state['equipment']['weapon'])) {
            $state['equipment']['weapon'] =
                WeaponNormalizer::normalize(
                    $state['equipment']['weapon']
                );
        }

        // Garante limites seguros de atributos
        foreach (['str', 'dex', 'con', 'int', 'wis', 'cha'] as $attr) {
            if (isset($state['abilities'][$attr])) {
                $state['abilities'][$attr] = max(
                    1,
                    min(20, (int) $state['abilities'][$attr])
                );
            }
        }

        return $state;
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
Você é um especialista em Dungeons & Dragons 5ª edição.

Sua tarefa é extrair dados estruturados de uma ficha de personagem.

REGRAS OBRIGATÓRIAS:

1. Nunca invente valores.
2. Se não encontrar um dado, retorne null.
3. Use apenas valores permitidos.

Classes válidas:
artificer, barbarian, bard, cleric, druid,
fighter, monk, paladin, ranger,
rogue, sorcerer, warlock, wizard

Espécies válidas:
humano, anao, elfo, halfling,
gnomo, meio-elfo, meio-orc, tiefling

Mapeamento obrigatório:
Armor Class → ac
Hit Points → hp_max
Current HP → hp_current
Strength → str
Dexterity → dex
Constitution → con
Intelligence → int
Wisdom → wis
Charisma → cha

Armas devem ser retornadas em formato normalizado:
espada_longa, espada_curta, adaga,
arco_longo, arco_curto,
machado_grande, martelo_guerra

Não retorne texto explicativo.
Retorne apenas JSON compatível com o schema.
PROMPT;
    }
}
