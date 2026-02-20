<?php

declare(strict_types=1);

namespace App\Http\Requests\GameSession;

use App\Domain\Combat\Enums\ActionType;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ProcessTurnRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'player_action' => ['required', 'string', Rule::enum(ActionType::class)],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'player_action' => 'ação do jogador',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'player_action.enum' => 'A :attribute deve ser uma das seguintes: attack, defend, flee, wait.',
        ];
    }
}
