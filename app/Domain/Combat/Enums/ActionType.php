<?php

declare(strict_types=1);

namespace App\Domain\Combat\Enums;

enum ActionType: string
{
    case Attack = 'attack';
    case Defend = 'defend';
    case Flee = 'flee';
    case Wait = 'wait';
}
