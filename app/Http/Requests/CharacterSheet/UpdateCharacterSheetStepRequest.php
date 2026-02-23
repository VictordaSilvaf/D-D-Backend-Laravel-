<?php

declare(strict_types=1);

namespace App\Http\Requests\CharacterSheet;

use App\Http\Requests\ApiRequest;

class UpdateCharacterSheetStepRequest extends ApiRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'state' => ['required', 'array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'state' => 'dados da etapa',
        ];
    }
}
