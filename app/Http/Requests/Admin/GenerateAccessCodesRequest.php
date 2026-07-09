<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class GenerateAccessCodesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isRoot();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'formation_id' => ['required', 'integer', 'exists:formations,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:200'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
