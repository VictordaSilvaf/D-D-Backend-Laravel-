<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Combat\Contracts\DiceRoller;

final class FakeDiceRoller implements DiceRoller
{
    public function __construct(
        private readonly int $value
    ) {}

    public function roll(): int
    {
        return $this->value;
    }
}
