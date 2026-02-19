<?php

declare(strict_types=1);

namespace App\Domain\Turn\Contracts;

interface DiceRoller
{
    public function d20(): int;
}
