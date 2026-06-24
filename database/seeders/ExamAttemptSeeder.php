<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ExamAttempt;
use Illuminate\Database\Seeder;

final class ExamAttemptSeeder extends Seeder
{
    public function run(): void
    {
        // Paul (user 4) - Exam 1 (Full Stack) - réussi
        ExamAttempt::query()->create([
            'user_id' => 4,
            'exam_id' => 1,
            'attempt_number' => 1,
            'score' => 85,
            'max_score' => 100,
            'percentage' => 85.00,
            'status' => 'completed',
            'started_at' => now()->subWeeks(2)->subHours(2),
            'completed_at' => now()->subWeeks(2)->subHours(1),
            'time_taken' => 3600,
        ]);

        // Paul (user 4) - Exam 2 (Marketing) - en cours
        ExamAttempt::query()->create([
            'user_id' => 4,
            'exam_id' => 2,
            'attempt_number' => 1,
            'score' => 0,
            'max_score' => 100,
            'percentage' => 0,
            'status' => 'in_progress',
            'started_at' => now()->subHours(1),
            'completed_at' => null,
            'time_taken' => null,
        ]);

        // Marie (user 5) - Exam 2 (Marketing) - échoué
        ExamAttempt::query()->create([
            'user_id' => 5,
            'exam_id' => 2,
            'attempt_number' => 1,
            'score' => 45,
            'max_score' => 100,
            'percentage' => 45.00,
            'status' => 'failed',
            'started_at' => now()->subDays(3)->subHours(2),
            'completed_at' => now()->subDays(3)->subHours(1),
            'time_taken' => 3600,
        ]);

        // Marie (user 5) - Exam 2 (Marketing) - tentative 2
        ExamAttempt::query()->create([
            'user_id' => 5,
            'exam_id' => 2,
            'attempt_number' => 2,
            'score' => 0,
            'max_score' => 100,
            'percentage' => 0,
            'status' => 'in_progress',
            'started_at' => now(),
            'completed_at' => null,
            'time_taken' => null,
        ]);

        // Alice (user 7) - Exam 1 (Full Stack) - réussi
        ExamAttempt::query()->create([
            'user_id' => 7,
            'exam_id' => 1,
            'attempt_number' => 1,
            'score' => 92,
            'max_score' => 100,
            'percentage' => 92.00,
            'status' => 'completed',
            'started_at' => now()->subDays(10)->subHours(2),
            'completed_at' => now()->subDays(10)->subHours(1),
            'time_taken' => 3300,
        ]);

        // David (user 8) - Exam 1 (Full Stack) - échoué
        ExamAttempt::query()->create([
            'user_id' => 8,
            'exam_id' => 1,
            'attempt_number' => 1,
            'score' => 55,
            'max_score' => 100,
            'percentage' => 55.00,
            'status' => 'failed',
            'started_at' => now()->subDays(5)->subHours(2),
            'completed_at' => now()->subDays(5)->subHours(1),
            'time_taken' => 2400,
        ]);

        // David (user 8) - Exam 1 (Full Stack) - tentative 2, en cours
        ExamAttempt::query()->create([
            'user_id' => 8,
            'exam_id' => 1,
            'attempt_number' => 2,
            'score' => 0,
            'max_score' => 100,
            'percentage' => 0,
            'status' => 'in_progress',
            'started_at' => now()->subMinutes(30),
            'completed_at' => null,
            'time_taken' => null,
        ]);
    }
}
