<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ChapterTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        ];
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
        ];
    }
}
