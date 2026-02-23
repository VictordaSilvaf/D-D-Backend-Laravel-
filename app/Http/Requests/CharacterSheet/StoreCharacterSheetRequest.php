<?php

declare(strict_types=1);

namespace App\Http\Requests\CharacterSheet;

use App\Http\Requests\ApiRequest;

class StoreCharacterSheetRequest extends ApiRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'state' => ['nullable', 'array'],
            'state.basics' => ['nullable', 'array'],
            'state.basics.character_name' => ['nullable', 'string', 'max:255'],
            'state.basics.player_name' => ['nullable', 'string', 'max:255'],
            'state.basics.species' => ['nullable', 'string', 'max:100'],
            'state.class' => ['nullable', 'array'],
            'state.abilities' => ['nullable', 'array'],
            'state.background' => ['nullable', 'array'],
            'state.combat' => ['nullable', 'array'],
            'state.equipment' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'state' => 'estado da ficha',
        ];
    }
}
