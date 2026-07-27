<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MobileMoneyCountryEnum;
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
            'payment_method' => ['required', 'string', Rule::in(['card', 'mobile_money'])],
            'mobile_money_country' => [
                'nullable',
                'required_if:payment_method,mobile_money',
                Rule::enum(MobileMoneyCountryEnum::class),
            ],
            'mobile_money_phone' => [
                'nullable',
                'required_if:payment_method,mobile_money',
                'string',
                'max:20',
                'regex:/^\\+[1-9]\\d{7,14}$/',
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
            'payment_method.in' => 'Veuillez choisir un moyen de paiement valide.',
            'mobile_money_country.required_if' => 'Veuillez choisir le pays de votre numéro Mobile Money.',
            'mobile_money_country.enum' => 'Le pays Mobile Money choisi est invalide.',
            'mobile_money_phone.required_if' => 'Veuillez renseigner votre numéro Mobile Money.',
            'mobile_money_phone.regex' => 'Utilisez un numéro international valide, par exemple +243XXXXXXXXX.',
        ];
    }
}
