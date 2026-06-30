<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\FormationLevelEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class FormationRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    final public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'duration_hours' => ['required', 'integer', 'min:0'],
            'difficulty_level' => ['required', Rule::enum(FormationLevelEnum::class)],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'sections' => ['nullable', 'array'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.title' => ['required', 'string', 'max:255', 'distinct'],
            'sections.*.description' => ['nullable', 'string'],
            'sections.*.duration' => ['nullable', 'integer', 'min:0'],
            'sections.*.is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    final public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'duration_hours.required' => 'La durée (en heures) est obligatoire.',
            'difficulty_level.required' => 'Le niveau de difficulté est obligatoire.',
            'image.image' => 'Le fichier doit être une image.',
            'image.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ];
    }
}
