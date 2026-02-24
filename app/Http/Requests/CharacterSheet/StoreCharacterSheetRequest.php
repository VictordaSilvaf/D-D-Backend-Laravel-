<?php

declare(strict_types=1);

namespace App\Http\Requests\CharacterSheet;

use App\Domain\Character\CharacterSheetRules;
use App\Http\Requests\ApiRequest;

final class StoreCharacterSheetRequest extends ApiRequest
{
    public function rules(): array
    {
        return CharacterSheetRules::forCreate();
    }

    public function attributes(): array
    {
        return [
            'state' => 'estado da ficha',
        ];
    }
}
