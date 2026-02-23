<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Turn\CombatStateArrayMapper;
use App\Domain\Turn\TurnEngine;
use App\Domain\Turn\TurnResult;
use App\Models\CharacterSheet;
use App\Models\GameSession;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class GameSessionService
{
    public function __construct(
        private readonly TurnEngine $turnEngine,
        private readonly CombatStateArrayMapper $mapper,
    ) {}

    /**
     * Create a new game session with initial combat state.
     * If character_sheet_id is provided and belongs to user, player stats are taken from the sheet.
     *
     * @param  array<string, mixed>  $enemyStats
     */
    public function createSession(int $userId, array $enemyStats, ?int $characterSheetId = null): GameSession
    {
        $playerStats = [
            'hp' => 30,
            'defense' => 10,
            'damage' => 5,
        ];

        if ($characterSheetId !== null) {
            $sheet = CharacterSheet::where('user_id', $userId)->where('id', $characterSheetId)->first();
            if ($sheet instanceof CharacterSheet) {
                $playerStats = $sheet->toCombatPlayerState();
            }
        }

        $initialState = [
            'player' => $playerStats,
            'enemy' => $enemyStats,
            'player_action' => 'wait',
            'combat_log' => [],
            'combat_ended' => false,
        ];

        Log::info('Game session created', [
            'user_id' => $userId,
            'character_sheet_id' => $characterSheetId,
            'initial_state' => $initialState,
        ]);

        return GameSession::create([
            'user_id' => $userId,
            'character_sheet_id' => $characterSheetId,
            'state' => $initialState,
            'combat_ended' => false,
        ]);
    }

    /**
     * Process a turn for a game session
     */
    public function processTurn(GameSession $session, string $playerAction): TurnResult
    {
        if ($session->combat_ended) {
            throw new RuntimeException('Combat already ended.');
        }

        $currentState = $session->state;

        Log::info('Processing turn', [
            'session_id' => $session->id,
            'player_action' => $playerAction,
        ]);

        // Process turn through engine
        $turnResult = $this->turnEngine->process($currentState, $playerAction);

        // Update session with new state
        $session->state = $turnResult->updatedState;
        $session->combat_ended = $turnResult->updatedState['combat_ended'] ?? false;
        $session->save();

        Log::info('Turn processed', [
            'session_id' => $session->id,
            'combat_ended' => $session->combat_ended,
        ]);

        return $turnResult;
    }

    /**
     * Get combat logs from a session
     *
     * @return array<int, string>
     */
    public function getLogs(GameSession $session): array
    {
        return $session->state['combat_log'] ?? [];
    }
}
