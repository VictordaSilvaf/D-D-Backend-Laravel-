<?php

declare(strict_types=1);

namespace App\Domain\Combat\Contracts;

interface DiceRoller
{
    public function roll(): int;
}
