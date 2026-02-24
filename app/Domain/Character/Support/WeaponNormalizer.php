<?php

declare(strict_types=1);

namespace App\Domain\Character\Support;

final class WeaponNormalizer
{
    private const MAP = [
        'longsword' => 'espada_longa',
        'long sword' => 'espada_longa',
        'espada longa' => 'espada_longa',

        'shortsword' => 'espada_curta',
        'short sword' => 'espada_curta',
        'espada curta' => 'espada_curta',

        'dagger' => 'adaga',
        'adaga' => 'adaga',

        'longbow' => 'arco_longo',
        'arco longo' => 'arco_longo',

        'shortbow' => 'arco_curto',
        'arco curto' => 'arco_curto',

        'warhammer' => 'martelo_guerra',
        'battleaxe' => 'machado_grande',
    ];

    public static function normalize(?string $weapon): ?string
    {
        if (! $weapon) {
            return null;
        }

        $clean = strtolower(trim($weapon));

        // Remove +1, +2 etc
        $clean = preg_replace('/\+\d+/', '', $clean);
        $clean = trim($clean);

        return self::MAP[$clean] ?? null;
    }
}
