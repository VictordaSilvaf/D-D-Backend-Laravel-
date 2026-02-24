<?php

declare(strict_types=1);

namespace App\Domain\Character\AI;

final class CharacterSheetAiSchema
{
    public array $state = [
        'basics' => [
            'character_name' => null,
            'player_name' => null,
            'species' => null,
        ],
        'class' => [
            'name' => null,
            'level' => null,
        ],
        'abilities' => [
            'str' => null,
            'dex' => null,
            'con' => null,
            'int' => null,
            'wis' => null,
            'cha' => null,
        ],
        'combat' => [
            'ac' => null,
            'hp_max' => null,
            'hp_current' => null,
            'damage_bonus' => null,
        ],
        'equipment' => [
            'weapon' => null,
            'armor' => null,
            'shield' => null,
            'items' => [],
        ],
        'background' => [],
    ];
}
