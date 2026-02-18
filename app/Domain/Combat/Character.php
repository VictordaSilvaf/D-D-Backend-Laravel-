<?php

declare(strict_types=1);

namespace App\Domain\Combat;

final class Character
{
    public function __construct(
        public int $hp,
        public readonly int $defense,
        public readonly int $damage,
    ) {}

    public function takeDamage(int $amount): void
    {
        $this->hp = max(0, $this->hp - $amount);
    }

    public function isAlive(): bool
    {
        return $this->hp > 0;
    }
}
