<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ChapterTypeEnum;
use App\Enums\QuestionTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class SectionRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    final public function rules(): array
    {
        return [
            'formation_id' => ['required', 'integer', Rule::exists('formations', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'chapters' => ['nullable', 'array'],
            'chapters.*.id' => ['nullable', 'integer'],
            'chapters.*.title' => ['required', 'string', 'max:255', 'distinct'],
            'chapters.*.content_type' => ['required', Rule::enum(ChapterTypeEnum::class)],
            'chapters.*.content' => ['nullable', 'string'],
            'chapters.*.video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime', 'max:512000'],
            'chapters.*.media' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'chapters.*.duration_minutes' => ['nullable', 'integer', 'min:0'],
            'chapters.*.is_free' => ['boolean'],
            'chapters.*.is_active' => ['boolean'],
            'exam' => ['nullable', 'array'],
            'exam.title' => ['nullable', 'string', 'max:255'],
            'exam.description' => ['nullable', 'string'],
            'exam.instructions' => ['nullable', 'string'],
            'exam.duration_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'exam.passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'exam.max_attempts' => ['nullable', 'integer', 'min:0', 'max:100'],
            'exam.randomize_questions' => ['boolean'],
            'exam.show_results_immediately' => ['boolean'],
            'exam.is_active' => ['boolean'],
            'exam.questions' => ['nullable', 'array'],
            'exam.questions.*.id' => ['nullable', 'integer'],
            'exam.questions.*.question_text' => ['required_with:exam.title', 'string'],
            'exam.questions.*.question_type' => ['required_with:exam.title', Rule::enum(QuestionTypeEnum::class)],
            'exam.questions.*.points' => ['required_with:exam.title', 'integer', 'min:1', 'max:100'],
            'exam.questions.*.is_required' => ['boolean'],
            'exam.questions.*.explanation' => ['nullable', 'string'],
            'exam.questions.*.options' => ['required_with:exam.title', 'array', 'min:2', 'max:5'],
            'exam.questions.*.options.*.option_text' => ['required_with:exam.title', 'string', 'max:500'],
            'exam.questions.*.options.*.is_correct' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    final public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ($this->input('exam.questions', []) as $index => $question) {
                $type = $question['question_type'] ?? null;
                $options = $question['options'] ?? [];
                $expectedMinimum = $type === QuestionTypeEnum::TRUE_FALSE->value ? 2 : 4;

                if (count($options) < $expectedMinimum) {
                    $validator->errors()->add("exam.questions.{$index}.options", "Cette question requiert au moins {$expectedMinimum} options.");
                }

                if ($type === QuestionTypeEnum::TRUE_FALSE->value && count($options) !== 2) {
                    $validator->errors()->add("exam.questions.{$index}.options", 'Une question Vrai/Faux doit contenir exactement deux options.');
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
            'formation_id.required' => 'La formation est obligatoire.',
            'formation_id.exists' => 'La formation sélectionnée est introuvable.',
            'title.required' => 'Le titre de la section est obligatoire.',
            'chapters.*.title.required' => 'Le titre du chapitre est obligatoire.',
            'chapters.*.title.distinct' => 'Deux chapitres ne peuvent pas avoir le même titre.',
            'chapters.*.content_type.required' => 'Le type de contenu est obligatoire.',
            'chapters.*.media.mimes' => 'Le fichier doit être un PDF.',
            'chapters.*.media.max' => 'Le PDF ne doit pas dépasser 50 Mo.',
            'chapters.*.video.mimetypes' => 'Le fichier doit être une vidéo (mp4, webm, ogg, mov).',
            'chapters.*.video.max' => 'La vidéo ne doit pas dépasser 500 Mo.',
            'exam.questions.*.question_text.required_with' => 'Le texte de la question est obligatoire.',
            'exam.questions.*.question_type.required_with' => 'Le type de question est obligatoire.',
            'exam.questions.*.points.required_with' => 'Les points sont obligatoires.',
            'exam.questions.*.options.required_with' => 'Les options de réponse sont obligatoires.',
            'exam.questions.*.options.min' => 'Au moins 2 options sont requises.',
            'exam.questions.*.options.max' => 'Maximum 5 options autorisées.',
        ];
    }
}
