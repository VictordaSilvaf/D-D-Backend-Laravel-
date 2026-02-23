<?php

declare(strict_types=1);

namespace App\Http\Requests\CharacterSheet;

use App\Http\Requests\ApiRequest;

class ImportCharacterSheetPdfRequest extends ApiRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf', 'max:5120'], // 5 MB
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'file' => 'arquivo PDF',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'file.mimes' => 'O arquivo deve ser um PDF.',
            'file.max' => 'O arquivo PDF não pode ter mais de 5 MB.',
        ];
    }
}
