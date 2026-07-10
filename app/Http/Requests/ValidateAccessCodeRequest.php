<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ValidateAccessCodeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => mb_strtoupper(mb_trim((string) $this->input('code'))),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'min:6', 'max:64', 'regex:/^[A-Z0-9-]+$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Veuillez saisir votre code d\'accès.',
            'code.min' => 'Le code d\'accès doit contenir au moins 6 caractères.',
            'code.max' => 'Le code d\'accès est trop long.',
            'code.regex' => 'Le code d\'accès contient des caractères non autorisés.',
        ];
    }
}
