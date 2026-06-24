<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserProfile;
use Illuminate\Database\Seeder;

final class UserProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            ['user_id' => 1, 'bio' => 'Administrateur principal de la plateforme IRMA Learning.', 'profession' => 'Administrateur système', 'city' => 'Douala', 'country' => 'Cameroun', 'preferences' => ['notifications' => true, 'language' => 'fr']],
            ['user_id' => 2, 'bio' => 'Formateur en développement web et mobile avec plus de 8 ans d\'expérience.', 'profession' => 'Développeur Full Stack', 'linkedin' => 'https://linkedin.com/in/jean-mbele', 'city' => 'Yaoundé', 'country' => 'Cameroun', 'preferences' => ['notifications' => true, 'language' => 'fr']],
            ['user_id' => 3, 'bio' => 'Experte en marketing digital et stratégies de croissance.', 'profession' => 'Marketeur digital', 'linkedin' => 'https://linkedin.com/in/sarah-nkwi', 'city' => 'Douala', 'country' => 'Cameroun', 'preferences' => ['notifications' => true, 'language' => 'fr']],
            ['user_id' => 4, 'bio' => 'Étudiant en informatique, passionné par le développement web.', 'profession' => 'Étudiant', 'city' => 'Yaoundé', 'country' => 'Cameroun', 'preferences' => ['notifications' => true, 'language' => 'fr']],
            ['user_id' => 5, 'bio' => 'Je souhaite me former au marketing digital pour développer mon activité.', 'profession' => 'Entrepreneure', 'city' => 'Douala', 'country' => 'Cameroun', 'preferences' => ['notifications' => true, 'language' => 'fr']],
            ['user_id' => 6, 'bio' => 'Ingénieur en reconversion vers la data science.', 'profession' => 'Ingénieur', 'linkedin' => 'https://linkedin.com/in/eric-kamga', 'city' => 'Bafoussam', 'country' => 'Cameroun', 'preferences' => ['notifications' => true, 'language' => 'fr']],
            ['user_id' => 7, 'bio' => 'Étudiante en licence informatique.', 'profession' => 'Étudiante', 'city' => 'Buea', 'country' => 'Cameroun', 'preferences' => ['notifications' => true, 'language' => 'en']],
            ['user_id' => 8, 'bio' => 'Je me forme pour devenir développeur web professionnel.', 'profession' => 'Développeur junior', 'city' => 'Garoua', 'country' => 'Cameroun', 'preferences' => ['notifications' => true, 'language' => 'fr']],
        ];

        foreach ($profiles as $profile) {
            UserProfile::query()->create($profile);
        }
    }
}
