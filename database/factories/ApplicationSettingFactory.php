<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ApplicationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationSetting>
 */
final class ApplicationSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'app_name' => 'IRMA Learning',
            'app_tagline' => 'Développez vos compétences professionnelles.',
            'support_email' => $this->faker->companyEmail(),
            'logo_path' => null,
            'primary_color' => '#a23362',
            'default_currency' => 'USD',
            'allow_registration' => true,
            'maintenance_message' => null,
            'certificate_signature_name' => $this->faker->name(),
        ];
    }
}
