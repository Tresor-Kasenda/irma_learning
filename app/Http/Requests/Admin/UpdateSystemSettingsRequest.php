<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isRoot();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:100'],
            'app_tagline' => ['nullable', 'string', 'max:180'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_address_primary' => ['nullable', 'string', 'max:255'],
            'contact_address_secondary' => ['nullable', 'string', 'max:255'],
            'home_hero_title' => ['nullable', 'string', 'max:180'],
            'home_hero_subtitle' => ['nullable', 'string', 'max:180'],
            'home_features' => ['sometimes', 'array', 'size:3'],
            'home_features.*' => ['required', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'default_currency' => ['required', 'string', 'size:3'],
            'allow_registration' => ['required', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'certificate_signature_name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
