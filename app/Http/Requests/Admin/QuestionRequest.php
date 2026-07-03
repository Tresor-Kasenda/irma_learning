<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\QuestionTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class QuestionRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    final public function rules(): array
    {
        return [
            'question_text' => ['required', 'string'],
            'question_type' => ['required', Rule::enum(QuestionTypeEnum::class)],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'is_required' => ['boolean'],
            'explanation' => ['nullable', 'string'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.option_text' => ['required', 'string', 'max:500'],
            'options.*.is_correct' => ['required', 'boolean'],
            'options.*.order_position' => ['integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    final public function messages(): array
    {
        return [
            'question_text.required' => 'Le texte de la question est obligatoire.',
            'question_type.required' => 'Le type de question est obligatoire.',
            'points.required' => 'Les points sont obligatoires.',
            'options.required' => 'Au moins 2 options sont requises.',
            'options.min' => 'Au moins 2 options sont requises.',
            'options.*.option_text.required' => 'Le texte de l\'option est obligatoire.',
        ];
    }
}
