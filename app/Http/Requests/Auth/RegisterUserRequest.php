<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'lowercase',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9][a-z0-9._-]*$/',
                Rule::unique(User::class, 'username'),
            ],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.lowercase' => "Le nom d'utilisateur doit être en minuscules.",
            'username.min' => "Le nom d'utilisateur doit contenir au moins :min caractères.",
            'username.regex' => "Le nom d'utilisateur peut contenir des lettres, chiffres, points, tirets et tirets bas.",
            'username.unique' => "Ce nom d'utilisateur est déjà utilisé.",
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'password.mixed' => 'Le mot de passe doit contenir une majuscule et une minuscule.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le mot de passe doit contenir au moins un symbole.',
        ];
    }
}
