<?php

declare(strict_types=1);

namespace App\Domain\Character;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class CharacterSheetRules
{
    public static function full(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | ROOT
            |--------------------------------------------------------------------------
            */
            'state' => ['required', 'array'],

            /*
            |--------------------------------------------------------------------------
            | BASICS
            |--------------------------------------------------------------------------
            */
            'state.basics' => ['nullable', 'array'],
            'state.basics.character_name' => ['required_with:state.basics', 'string', 'max:255'],
            'state.basics.player_name' => ['nullable', 'string', 'max:255'],
            'state.basics.species' => [
                'required_with:state.basics',
                'string',
                Rule::in(self::species()),
            ],

            /*
            |--------------------------------------------------------------------------
            | CLASS
            |--------------------------------------------------------------------------
            */
            'state.class' => ['nullable', 'array'],
            'state.class.name' => [
                'required_with:state.class',
                'string',
                new Enum(CharacterClass::class),
            ],
            'state.class.level' => [
                'required_with:state.class',
                'integer',
                'min:1',
                'max:20',
            ],

            /*
            |--------------------------------------------------------------------------
            | ABILITIES
            |--------------------------------------------------------------------------
            */
            'state.abilities' => ['nullable', 'array'],

            'state.abilities.str' => ['required_with:state.abilities', 'integer', 'min:1', 'max:30'],
            'state.abilities.dex' => ['required_with:state.abilities', 'integer', 'min:1', 'max:30'],
            'state.abilities.con' => ['required_with:state.abilities', 'integer', 'min:1', 'max:30'],
            'state.abilities.int' => ['required_with:state.abilities', 'integer', 'min:1', 'max:30'],
            'state.abilities.wis' => ['required_with:state.abilities', 'integer', 'min:1', 'max:30'],
            'state.abilities.cha' => ['required_with:state.abilities', 'integer', 'min:1', 'max:30'],

            /*
            |--------------------------------------------------------------------------
            | BACKGROUND
            |--------------------------------------------------------------------------
            */
            'state.background' => ['nullable', 'array'],

            /*
            |--------------------------------------------------------------------------
            | COMBAT
            |--------------------------------------------------------------------------
            */
            'state.combat' => ['nullable', 'array'],
            'state.combat.ac' => ['required_with:state.combat', 'integer', 'min:0', 'max:30'],
            'state.combat.hp_max' => ['required_with:state.combat', 'integer', 'min:1', 'max:500'],
            'state.combat.hp_current' => [
                'required_with:state.combat',
                'integer',
                'min:0',
                'lte:state.combat.hp_max',
            ],
            'state.combat.damage_bonus' => ['nullable', 'integer', 'min:-10', 'max:100'],

            /*
            |--------------------------------------------------------------------------
            | EQUIPMENT
            |--------------------------------------------------------------------------
            */
            'state.equipment' => ['nullable', 'array'],

            'state.equipment.weapon' => [
                'nullable',
                'string',
                Rule::in(self::weapons()),
            ],

            'state.equipment.armor' => [
                'nullable',
                'string',
                Rule::in(self::armors()),
            ],

            'state.equipment.shield' => ['nullable', 'boolean'],

            'state.equipment.items' => ['nullable', 'array'],
            'state.equipment.items.*' => ['string', 'max:100'],
        ];
    }

    public static function forStep(CharacterSheetStep $step): array
    {
        return match ($step) {

            /*
        |--------------------------------------------------------------------------
        | BASICS
        |--------------------------------------------------------------------------
        */
            CharacterSheetStep::BASICS => [
                'character_name' => ['required', 'string', 'max:255'],
                'player_name' => ['nullable', 'string', 'max:255'],
                'species' => [
                    'required',
                    new Enum(CharacterSpecies::class),
                ],
            ],

            /*
        |--------------------------------------------------------------------------
        | CLASS / ARCHETYPE
        |--------------------------------------------------------------------------
        */
            CharacterSheetStep::ARCHETYPE => [
                'name' => [
                    'required',
                    new Enum(CharacterClass::class),
                ],
                'level' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:20',
                ],
            ],

            /*
        |--------------------------------------------------------------------------
        | ABILITIES
        |--------------------------------------------------------------------------
        */
            CharacterSheetStep::ABILITIES => [
                'str' => ['required', 'integer', 'min:1', 'max:20'],
                'dex' => ['required', 'integer', 'min:1', 'max:20'],
                'con' => ['required', 'integer', 'min:1', 'max:20'],
                'int' => ['required', 'integer', 'min:1', 'max:20'],
                'wis' => ['required', 'integer', 'min:1', 'max:20'],
                'cha' => ['required', 'integer', 'min:1', 'max:20'],
            ],

            /*
        |--------------------------------------------------------------------------
        | BACKGROUND
        |--------------------------------------------------------------------------
        */
            CharacterSheetStep::BACKGROUND => [
                'origin' => ['required', 'string', 'max:500'],
                'personality' => ['nullable', 'string', 'max:500'],
            ],

            /*
        |--------------------------------------------------------------------------
        | COMBAT
        |--------------------------------------------------------------------------
        */
            CharacterSheetStep::COMBAT => [
                'ac' => ['required', 'integer', 'min:0', 'max:30'],
                'hp_max' => ['required', 'integer', 'min:1', 'max:500'],
                'hp_current' => [
                    'required',
                    'integer',
                    'min:0',
                    'lte:hp_max',
                ],
                'damage_bonus' => ['nullable', 'integer', 'min:-10', 'max:100'],
            ],

            /*
        |--------------------------------------------------------------------------
        | EQUIPMENT
        |--------------------------------------------------------------------------
        */
            CharacterSheetStep::EQUIPMENT => [
                'weapon' => ['nullable', 'string'], // ideal: Enum(Weapon::class)
                'armor' => ['nullable', 'string'],  // ideal: Enum(Armor::class)
                'shield' => ['nullable', 'boolean'],
                'items' => ['nullable', 'array'],
                'items.*' => ['string', 'max:100'],
            ],

            /*
        |--------------------------------------------------------------------------
        | REVIEW
        |--------------------------------------------------------------------------
        */
            CharacterSheetStep::REVIEW => [
                // normalmente review não altera dados estruturais
                // mas pode validar confirmação final
                'confirm' => ['required', 'boolean', 'accepted'],
            ],
        };
    }

    private static function species(): array
    {
        return [
            'humano',
            'anao',
            'elfo',
            'halfling',
            'gnomo',
            'meio-elfo',
            'meio-orc',
            'tiefling',
        ];
    }

    private static function weapons(): array
    {
        return array_merge(
            [
                'clava',
                'adaga',
                'machadinha',
                'lanca',
                'cajado',
                'besta_leve',
                'arco_curto',
            ],
            [
                'espada_longa',
                'espada_curta',
                'machado_grande',
                'martelo_guerra',
                'alabarda',
                'arco_longo',
                'besta_pesada',
            ]
        );
    }

    private static function armors(): array
    {
        return [
            'sem_armadura',
            'couro',
            'couro_batido',
            'cota_malha',
            'meia_armadura',
            'armadura_placas',
        ];
    }

    public static function forCreate(): array
    {
        return [
            'state' => ['nullable', 'array'],
        ];
    }
}
