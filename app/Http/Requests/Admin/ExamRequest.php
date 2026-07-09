<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\QuestionTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class ExamRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    final public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['required', 'integer', 'min:0', 'max:100'],
            'randomize_questions' => ['boolean'],
            'show_results_immediately' => ['boolean'],
            'is_active' => ['boolean'],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after_or_equal:available_from'],
            'examable_type' => ['required', 'string', 'in:App\Models\Section,App\Models\Formation'],
            'examable_id' => ['required', 'integer'],
            'questions' => ['nullable', 'array'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.question_type' => ['required', Rule::enum(QuestionTypeEnum::class)],
            'questions.*.points' => ['required', 'integer', 'min:1', 'max:100'],
            'questions.*.is_required' => ['boolean'],
            'questions.*.explanation' => ['nullable', 'string'],
            'questions.*.options' => ['required', 'array', 'min:2', 'max:5'],
            'questions.*.options.*.option_text' => ['required', 'string', 'max:500'],
            'questions.*.options.*.is_correct' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    final public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ($this->input('questions', []) as $index => $question) {
                $type = $question['question_type'] ?? null;
                $options = $question['options'] ?? [];
                $expectedMinimum = $type === QuestionTypeEnum::TRUE_FALSE->value ? 2 : 4;

                if (count($options) < $expectedMinimum) {
                    $validator->errors()->add("questions.{$index}.options", "Cette question requiert au moins {$expectedMinimum} options.");
                }

                if ($type === QuestionTypeEnum::TRUE_FALSE->value && count($options) !== 2) {
                    $validator->errors()->add("questions.{$index}.options", 'Une question Vrai/Faux doit contenir exactement deux options.');
                }

                $correctOptions = collect($options)->where('is_correct', true)->count();

                if (in_array($type, [QuestionTypeEnum::SINGLE_CHOICE->value, QuestionTypeEnum::TRUE_FALSE->value], true) && $correctOptions !== 1) {
                    $validator->errors()->add("questions.{$index}.options", 'Cette question doit contenir exactement une bonne réponse.');
                }

                if ($type === QuestionTypeEnum::MULTIPLE_CHOICE->value && $correctOptions < 1) {
                    $validator->errors()->add("questions.{$index}.options", 'Cette question doit contenir au moins une bonne réponse.');
                }
            }
        }];
    }

    /**
     * @return array<string, string>
     */
    final public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'duration_minutes.required' => 'La durée (en minutes) est obligatoire.',
            'duration_minutes.min' => 'La durée doit être d\'au moins 1 minute.',
            'passing_score.required' => 'Le score de réussite est obligatoire.',
            'passing_score.min' => 'Le score minimum est 0.',
            'passing_score.max' => 'Le score maximum est 100.',
            'max_attempts.required' => 'Le nombre de tentatives est obligatoire.',
            'examable_type.required' => 'Le type d\'entité associée est obligatoire.',
            'examable_id.required' => 'L\'entité associée est obligatoire.',
            'questions.*.question_text.required' => 'Le texte de la question est obligatoire.',
            'questions.*.question_type.required' => 'Le type de question est obligatoire.',
            'questions.*.points.required' => 'Les points sont obligatoires.',
            'questions.*.options.required' => 'Les options de réponse sont obligatoires.',
            'questions.*.options.min' => 'Au moins 2 options sont requises.',
            'questions.*.options.max' => 'Maximum 5 options autorisées.',
        ];
    }
}
