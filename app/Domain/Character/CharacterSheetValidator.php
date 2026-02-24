<?php

declare(strict_types=1);

namespace App\Domain\Character;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class CharacterSheetValidator
{
    public static function validate(array $data): array
    {
        return Validator::make($data, CharacterSheetRules::full())->validate();
    }

    public static function validateStep(CharacterSheetStep $step, array $data): array
    {
        return Validator::make($data, CharacterSheetRules::forStep($step))->validate();
    }
}
