<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\Formation;
use Illuminate\Database\Seeder;

final class ExamSeeder extends Seeder
{
    public function run(): void
    {
        Exam::query()->create([
            'examable_type' => Formation::class,
            'examable_id' => 1,
            'title' => 'Examen Final - Développement Web Full Stack',
            'description' => 'Testez vos connaissances acquises tout au long de la formation.',
            'instructions' => 'Ce examen comporte 10 questions. Vous avez 60 minutes pour le compléter. La note de passage est de 70%. Lisez attentivement chaque question avant de répondre.',
            'duration_minutes' => 60,
            'passing_score' => 70,
            'max_attempts' => 3,
            'randomize_questions' => true,
            'show_results_immediately' => true,
            'is_active' => true,
            'available_from' => now()->subDay(),
            'available_until' => now()->addYear(),
        ]);

        Exam::query()->create([
            'examable_type' => Formation::class,
            'examable_id' => 2,
            'title' => 'Examen Final - Marketing Digital',
            'description' => 'Évaluez votre maîtrise des stratégies marketing digitales.',
            'instructions' => 'Ce examen comporte 8 questions. La durée est de 45 minutes. La note de passage est de 75%. Bonne chance !',
            'duration_minutes' => 45,
            'passing_score' => 75,
            'max_attempts' => 2,
            'randomize_questions' => true,
            'show_results_immediately' => true,
            'is_active' => true,
            'available_from' => now()->subDay(),
            'available_until' => now()->addYear(),
        ]);

        Exam::query()->create([
            'examable_type' => Formation::class,
            'examable_id' => 3,
            'title' => 'Examen Final - Data Science avec Python',
            'description' => 'Validez vos compétences en data science et machine learning.',
            'instructions' => 'Ce examen comporte 10 questions. Durée : 90 minutes. Note de passage : 80%. Certaines questions nécessitent des calculs, alors prenez votre temps.',
            'duration_minutes' => 90,
            'passing_score' => 80,
            'max_attempts' => 2,
            'randomize_questions' => false,
            'show_results_immediately' => false,
            'is_active' => true,
            'available_from' => now()->subDay(),
            'available_until' => now()->addYear(),
        ]);
    }
}
