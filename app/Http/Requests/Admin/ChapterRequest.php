<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ChapterTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ChapterRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    final public function rules(): array
    {
        return [
            'section_id' => ['required', 'integer', Rule::exists('sections', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content_type' => ['required', Rule::enum(ChapterTypeEnum::class)],
            'content' => ['nullable', 'string'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'is_free' => ['boolean'],
            'is_active' => ['boolean'],
            'video' => [
                'nullable',
                'file',
                'extensions:mp4,webm,ogg,mov',
                'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime,application/octet-stream',
                'max:512000',
            ],
            'media' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    final public function messages(): array
    {
        return [
            'section_id.required' => 'La section est obligatoire.',
            'section_id.exists' => 'La section sélectionnée est introuvable.',
            'title.required' => 'Le titre du chapitre est obligatoire.',
            'content_type.required' => 'Le type de contenu est obligatoire.',
            'media.mimes' => 'Le fichier doit être un PDF.',
            'media.max' => 'Le PDF ne doit pas dépasser 50 Mo.',
            'video.extensions' => 'Le fichier doit utiliser une extension vidéo autorisée (mp4, webm, ogg, mov).',
            'video.mimetypes' => 'Le fichier doit être une vidéo (mp4, webm, ogg, mov).',
            'video.max' => 'La vidéo ne doit pas dépasser 500 Mo.',
        ];
    }
}
