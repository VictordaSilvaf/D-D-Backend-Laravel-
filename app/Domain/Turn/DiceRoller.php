<?php

namespace App\Domain\Turn;

final class DiceRoller
{
    public function d20(): int
    {
        return random_int(1, 20);
    }
}
