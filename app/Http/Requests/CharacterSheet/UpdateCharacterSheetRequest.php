<?php

declare(strict_types=1);

namespace App\Http\Requests\CharacterSheet;

use App\Http\Requests\ApiRequest;

class UpdateCharacterSheetRequest extends ApiRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'state' => ['sometimes', 'array'],
            'state.basics' => ['nullable', 'array'],
            'state.basics.character_name' => ['nullable', 'string', 'max:255'],
            'state.basics.player_name' => ['nullable', 'string', 'max:255'],
            'state.basics.species' => ['nullable', 'string', 'max:100'],
            'state.class' => ['nullable', 'array'],
            'state.class.name' => ['nullable', 'string', 'max:100'],
            'state.class.level' => ['nullable', 'integer', 'min:1', 'max:20'],
            'state.abilities' => ['nullable', 'array'],
            'state.abilities.str' => ['nullable', 'integer', 'min:1', 'max:30'],
            'state.abilities.dex' => ['nullable', 'integer', 'min:1', 'max:30'],
            'state.abilities.con' => ['nullable', 'integer', 'min:1', 'max:30'],
            'state.abilities.int' => ['nullable', 'integer', 'min:1', 'max:30'],
            'state.abilities.wis' => ['nullable', 'integer', 'min:1', 'max:30'],
            'state.abilities.cha' => ['nullable', 'integer', 'min:1', 'max:30'],
            'state.background' => ['nullable', 'array'],
            'state.combat' => ['nullable', 'array'],
            'state.combat.ac' => ['nullable', 'integer', 'min:0', 'max:30'],
            'state.combat.hp_max' => ['nullable', 'integer', 'min:1', 'max:500'],
            'state.combat.hp_current' => ['nullable', 'integer', 'min:0', 'max:500'],
            'state.combat.damage_bonus' => ['nullable', 'integer', 'min:0', 'max:100'],
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
