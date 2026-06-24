<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

final class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'IRMA Learning'],
            ['key' => 'app_description', 'value' => 'Plateforme de formation en ligne'],
            ['key' => 'app_url', 'value' => 'https://irmalearning.com'],
            ['key' => 'default_currency', 'value' => 'XAF'],
            ['key' => 'locale', 'value' => 'fr'],
            ['key' => 'timezone', 'value' => 'Africa/Douala'],
            ['key' => 'max_exam_attempts', 'value' => '3'],
            ['key' => 'default_passing_score', 'value' => '70'],
            ['key' => 'enable_certificates', 'value' => 'true'],
            ['key' => 'enable_notifications', 'value' => 'true'],
            ['key' => 'maintenance_mode', 'value' => 'false'],
            ['key' => 'contact_email', 'value' => 'contact@irmalearning.com'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/irmalearning'],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/irmalearning'],
            ['key' => 'social_linkedin', 'value' => 'https://linkedin.com/company/irmalearning'],
            ['key' => 'about_us', 'value' => 'IRMA Learning est une plateforme de formation en ligne dédiée à l\'apprentissage des compétences numériques au Cameroun et en Afrique.'],
            ['key' => 'registration_open', 'value' => 'true'],
            ['key' => 'instructor_commission', 'value' => '70'],
        ];

        foreach ($settings as $setting) {
            Setting::query()->create($setting);
        }
    }
}
