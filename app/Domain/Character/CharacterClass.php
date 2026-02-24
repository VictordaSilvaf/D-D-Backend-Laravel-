<?php

declare(strict_types=1);

namespace App\Domain\Character;

enum CharacterClass: string
{
    case ARTIFICER = 'tecnomante';
    case BARBARO = 'barbaro';
    case BARDO = 'bardo';
    case CLERIGO = 'clerigo';
    case DRUIDA = 'druida';
    case GUERREIRO = 'guerreiro';
    case MONGE = 'monge';
    case PALADINO = 'paladino';
    case PATRULHEIRO = 'patrulheiro';
    case PÍCARO = 'picaro';
    case FEITICEIRO = 'feiticeiro';
    case BRUXO = 'bruxo';
    case MAGO = 'mago';
}
