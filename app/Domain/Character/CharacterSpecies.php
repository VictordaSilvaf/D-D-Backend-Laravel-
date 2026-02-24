<?php

declare(strict_types=1);

namespace App\Domain\Character;

enum CharacterSpecies: string
{
    case HUMANO = 'humano';
    case ANAO = 'anao';
    case ELFO = 'elfo';
    case HALFLING = 'halfling';
    case GNOMO = 'gnomo';
    case MEIO_ELFO = 'meio-elfo';
    case MEIO_ORC = 'meio-orc';
    case TIEFLING = 'tiefling';
}
