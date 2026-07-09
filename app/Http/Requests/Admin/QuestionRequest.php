<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\QuestionTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'options' => ['required', 'array', 'min:2', 'max:5'],
            'options.*.option_text' => ['required', 'string', 'max:500'],
            'options.*.is_correct' => ['required', 'boolean'],
            'options.*.order_position' => ['integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    final public function after(): array
    {
        return [function (Validator $validator): void {
            $type = $this->string('question_type')->toString();
            $options = $this->input('options', []);
            $expectedMinimum = $type === QuestionTypeEnum::TRUE_FALSE->value ? 2 : 4;

            if (count($options) < $expectedMinimum) {
                $validator->errors()->add('options', "Cette question requiert au moins {$expectedMinimum} options.");
            }

            if ($type === QuestionTypeEnum::TRUE_FALSE->value && count($options) !== 2) {
                $validator->errors()->add('options', 'Une question Vrai/Faux doit contenir exactement deux options.');
            }

            $correctOptions = collect($options)->where('is_correct', true)->count();

            if (in_array($type, [QuestionTypeEnum::SINGLE_CHOICE->value, QuestionTypeEnum::TRUE_FALSE->value], true) && $correctOptions !== 1) {
                $validator->errors()->add('options', 'Cette question doit contenir exactement une bonne réponse.');
            }

            if ($type === QuestionTypeEnum::MULTIPLE_CHOICE->value && $correctOptions < 1) {
                $validator->errors()->add('options', 'Cette question doit contenir au moins une bonne réponse.');
            }
        }];
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
            'options.max' => 'Maximum 5 options autorisées.',
            'options.*.option_text.required' => 'Le texte de l\'option est obligatoire.',
        ];
    }
}
