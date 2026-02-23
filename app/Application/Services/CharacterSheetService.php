<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Models\CharacterSheet;
use Illuminate\Support\Collection;

final class CharacterSheetService
{
    /**
     * @return Collection<int, CharacterSheet>
     */
    public function listForUser(int $userId, ?string $status = null): Collection
    {
        $query = CharacterSheet::where('user_id', $userId)->orderByDesc('updated_at');

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function create(int $userId, array $initialState = []): CharacterSheet
    {
        return CharacterSheet::create([
            'user_id' => $userId,
            'status' => CharacterSheet::STATUS_DRAFT,
            'state' => $initialState,
        ]);
    }

    public function update(CharacterSheet $sheet, array $state): CharacterSheet
    {
        $sheet->state = array_merge($sheet->state ?? [], $state);
        $sheet->save();

        return $sheet;
    }

    public function updateStep(CharacterSheet $sheet, string $step, array $stepState): CharacterSheet
    {
        $current = $sheet->state ?? [];
        $current[$step] = $stepState;
        $sheet->state = $current;
        $sheet->save();

        return $sheet;
    }

    public function complete(CharacterSheet $sheet): CharacterSheet
    {
        if ($sheet->status === CharacterSheet::STATUS_COMPLETED) {
            return $sheet;
        }

        $sheet->status = CharacterSheet::STATUS_COMPLETED;
        $sheet->save();

        return $sheet;
    }

    /**
     * Create a new character sheet from parsed PDF data.
     *
     * @param  array<string, mixed>  $parsedState
     */
    public function createFromImport(int $userId, array $parsedState): CharacterSheet
    {
        return $this->create($userId, $parsedState);
    }
}
