<?php

declare(strict_types=1);

namespace App\Domain\Turn\Contracts;

interface DiceRoller
{
    public function d4(): int;
    public function d6(): int;
    public function d8(): int;
    public function d10(): int;
    public function d12(): int;
    public function d20(): int;
    public function d100(): int;
}
