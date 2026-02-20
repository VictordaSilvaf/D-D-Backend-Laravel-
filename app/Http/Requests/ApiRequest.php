<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get custom attributes for validator errors in Portuguese.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Get custom messages for validator errors in Portuguese.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'email' => 'O campo :attribute deve ser um e-mail válido.',
            'min' => 'O campo :attribute deve ter pelo menos :min caracteres.',
            'max' => 'O campo :attribute não pode ter mais de :max caracteres.',
            'confirmed' => 'A confirmação do campo :attribute não corresponde.',
            'unique' => 'O valor do campo :attribute já está em uso.',
            'string' => 'O campo :attribute deve ser um texto.',
        ];
    }
}
