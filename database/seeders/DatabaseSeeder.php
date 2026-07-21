<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            UserProfileSeeder::class,
            FormationSeeder::class,
            SectionSeeder::class,
            ChapterSeeder::class,
            ExamSeeder::class,
            QuestionSeeder::class,
            QuestionOptionSeeder::class,
            ProfessionalDemoContentSeeder::class,
            BtpCmaContentSeeder::class,
            EnrollmentSeeder::class,
            ExamAttemptSeeder::class,
            UserAnswerSeeder::class,
            UserProgressSeeder::class,
            CertificateSeeder::class,
            SettingSeeder::class,
            VerificationCodeSeeder::class,
            FormationAccessCodeSeeder::class,
        ]);
    }
}
