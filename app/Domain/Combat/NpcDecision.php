<?php

declare(strict_types=1);

namespace App\Domain\Combat;

use App\Domain\Combat\Enums\ActionType;
use ValueError;
use Illuminate\Support\Facades\Log;

final readonly class NpcDecision
{
    public function __construct(
        public ActionType $action,
        public string $emotionalState = '',
        public string $strategy = '',
        public int $damage = 0,
    ) {}

    public static function fromAgentResponse(array $data): self
    {
        $intention = trim(strtolower($data['intention'] ?? 'wait'));

        try {
            $action = ActionType::from($intention);
        } catch (ValueError $e) {
            Log::warning("NPC intention inválido recebido: '{$intention}'. Usando 'wait' como fallback.", [
                'raw_data' => $data,
                'exception' => $e->getMessage(),
            ]);

            $action = ActionType::Wait;
        }

        return new self(
            action: $action,
            emotionalState: trim($data['emotional_state'] ?? ''),
            strategy: trim($data['strategy'] ?? ''),
            damage: (int) ($data['damage'] ?? 0),
        );
    }

    // Opcional: helper para debug ou narração
    public function toCombatLog(): string
    {
        return "NPC decidiu {$this->action->value} - {$this->emotionalState} - {$this->strategy}";
    }
}
