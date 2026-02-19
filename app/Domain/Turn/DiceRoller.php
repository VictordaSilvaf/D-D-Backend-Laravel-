<?php

declare(strict_types=1);

namespace App\Domain\Turn;

use App\Domain\Turn\Contracts\DiceRoller as DiceRollerContract;

final class DiceRoller implements DiceRollerContract
{
    public function d20(): int
    {
        return random_int(1, 20);
    }
}
