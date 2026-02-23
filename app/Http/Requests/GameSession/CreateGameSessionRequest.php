<?php

declare(strict_types=1);

namespace App\Http\Requests\GameSession;

use App\Http\Requests\ApiRequest;

class CreateGameSessionRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'enemy' => ['required', 'array'],
            'enemy.hp' => ['required', 'integer', 'min:1', 'max:1000'],
            'enemy.defense' => ['required', 'integer', 'min:1', 'max:30'],
            'enemy.damage' => ['required', 'integer', 'min:1', 'max:100'],
            'character_sheet_id' => ['nullable', 'integer', 'exists:character_sheets,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'enemy' => 'inimigo',
            'enemy.hp' => 'HP do inimigo',
            'enemy.defense' => 'defesa do inimigo',
            'enemy.damage' => 'dano do inimigo',
            'character_sheet_id' => 'ficha de personagem',
        ];
    }
}
