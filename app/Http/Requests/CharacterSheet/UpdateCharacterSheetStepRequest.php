<?php

declare(strict_types=1);

namespace App\Http\Requests\CharacterSheet;

use App\Domain\Character\CharacterSheetRules;
use App\Domain\Character\CharacterSheetStep;
use App\Http\Requests\ApiRequest;

final class UpdateCharacterSheetStepRequest extends ApiRequest
{
    public function rules(): array
    {
        $step = CharacterSheetStep::fromString(
            $this->route('step')
        );

        return CharacterSheetRules::forStep($step);
    }

    public function attributes(): array
    {
        return [
            'state' => 'dados da etapa',
        ];
    }
}
