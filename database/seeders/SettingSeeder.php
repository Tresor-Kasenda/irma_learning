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
            ['key' => 'app_name', 'value' => 'BTPCMA Learning'],
            ['key' => 'app_description', 'value' => 'Plateforme de formation BTP & Artisanat'],
            ['key' => 'app_url', 'value' => 'https://formations.btpcma.org'],
            ['key' => 'default_currency', 'value' => 'CDF'],
            ['key' => 'locale', 'value' => 'fr'],
            ['key' => 'timezone', 'value' => 'Africa/Kinshasa'],
            ['key' => 'max_exam_attempts', 'value' => '3'],
            ['key' => 'default_passing_score', 'value' => '70'],
            ['key' => 'enable_certificates', 'value' => 'true'],
            ['key' => 'enable_notifications', 'value' => 'true'],
            ['key' => 'maintenance_mode', 'value' => 'false'],
            ['key' => 'contact_email', 'value' => 'info@btpcma.org'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/btpcma'],
            ['key' => 'social_twitter', 'value' => 'https://x.com/BtpCma'],
            ['key' => 'social_linkedin', 'value' => 'https://linkedin.com/company/btpcma'],
            ['key' => 'about_us', 'value' => "BTPCMA Learning est la plateforme de formation en ligne du Club BTP & CMA, dédiée aux professionnels du Bâtiment et des Travaux Publics en RDC. Elle propose des formations techniques, des certifications professionnelles et des programmes de développement continu pour les entreprises structurées comme pour les artisans du secteur."],
            ['key' => 'registration_open', 'value' => 'true'],
            ['key' => 'instructor_commission', 'value' => '70'],
            ['key' => 'company_address', 'value' => '07, Av. OUA, Kinshasa - Ngaliema, RD Congo'],
            ['key' => 'company_phone', 'value' => '+243 974 078 656'],
            ['key' => 'company_rccm', 'value' => ''],
            ['key' => 'company_niu', 'value' => ''],
        ];

        foreach ($settings as $setting) {
            Setting::query()->create($setting);
        }
    }
}
