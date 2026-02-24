<?php

declare(strict_types=1);

namespace App\Domain\Character;

enum CharacterSheetStep: string
{
    case BASICS = 'basics';
    case ARCHETYPE = 'class';
    case ABILITIES = 'abilities';
    case BACKGROUND = 'background';
    case COMBAT = 'combat';
    case EQUIPMENT = 'equipment';
    case REVIEW = 'review';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom($value)
            ?? throw new \InvalidArgumentException("Etapa inválida: {$value}");
    }
}
