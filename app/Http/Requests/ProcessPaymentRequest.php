<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProcessPaymentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', Rule::in(['mobile_money', 'card'])],
            'operator' => ['nullable', 'string', Rule::in(['orange', 'airtel', 'mpesa', 'africell'])],
            'phone' => [
                'required_if:payment_method,mobile_money',
                'nullable',
                'string',
                'regex:/^[0-9+\s]{9,20}$/',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Veuillez choisir un moyen de paiement.',
            'payment_method.in' => 'Moyen de paiement invalide.',
            'phone.required_if' => 'Veuillez saisir le numéro de téléphone Mobile Money.',
            'phone.regex' => 'Le numéro de téléphone n\'est pas valide.',
        ];
    }
}
