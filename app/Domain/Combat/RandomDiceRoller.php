<?php

declare(strict_types=1);

namespace App\Domain\Combat;

use App\Domain\Combat\Contracts\DiceRoller;

final class RandomDiceRoller implements DiceRoller
{
    public function roll(): int
    {
        return random_int(1, 20);
    }
}
