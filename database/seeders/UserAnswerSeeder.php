<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserAnswer;
use Illuminate\Database\Seeder;

final class UserAnswerSeeder extends Seeder
{
    public function run(): void
    {
        // === Paul (attempt 1) - Exam 1 ===
        // Q1: HTML
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 1,
            'selected_option_id' => 1,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // Q2: Flexbox
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 2,
            'selected_option_id' => 5,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // Q3: enum
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 3,
            'selected_option_id' => 9,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // Q4: Middleware
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 4,
            'selected_option_id' => 13,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // Q5: Hooks (multiple) - selected useState, useEffect, useContext, useReducer
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 5,
            'selected_option_id' => null,
            'selected_options' => [17, 18, 19, 21],
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 15,
        ]);

        // Q6: Tailwind (V/F)
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 6,
            'selected_option_id' => 23,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 5,
        ]);

        // Q7: Essai
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 7,
            'selected_option_id' => null,
            'selected_options' => null,
            'answer_text' => 'GET récupère des données via l\'URL, elles sont visibles et limitées en taille. POST envoie des données dans le corps de la requête, sans limite de taille, et les données ne sont pas visibles dans l\'URL.',
            'is_correct' => true,
            'points_earned' => 15,
        ]);

        // Q8: Artisan migration
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 8,
            'selected_option_id' => 25,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // Q9: Vite (text)
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 9,
            'selected_option_id' => null,
            'selected_options' => null,
            'answer_text' => 'Rechargement à chaud rapide et builds optimisés.',
            'is_correct' => false,
            'points_earned' => 0,
        ]);

        // Q10: Forge
        UserAnswer::query()->create([
            'exam_attempt_id' => 1,
            'question_id' => 10,
            'selected_option_id' => 29,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // === Alice (attempt 5) - Exam 1 ===
        // Alice got 92% - let's make most correct but one mistake
        UserAnswer::query()->create([
            'exam_attempt_id' => 5,
            'question_id' => 1,
            'selected_option_id' => 1,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 5,
            'question_id' => 2,
            'selected_option_id' => 5,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 5,
            'question_id' => 3,
            'selected_option_id' => 9,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 5,
            'question_id' => 4,
            'selected_option_id' => 13,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // Alice missed useReducer in the multiple choice question
        UserAnswer::query()->create([
            'exam_attempt_id' => 5,
            'question_id' => 5,
            'selected_option_id' => null,
            'selected_options' => [17, 18, 19],
            'answer_text' => null,
            'is_correct' => false,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 5,
            'question_id' => 6,
            'selected_option_id' => 23,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 5,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 5,
            'question_id' => 8,
            'selected_option_id' => 25,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 5,
            'question_id' => 10,
            'selected_option_id' => 29,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // David (attempt 6) - failed (55%)
        UserAnswer::query()->create([
            'exam_attempt_id' => 6,
            'question_id' => 1,
            'selected_option_id' => 2,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => false,
            'points_earned' => 0,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 6,
            'question_id' => 2,
            'selected_option_id' => 5,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 6,
            'question_id' => 3,
            'selected_option_id' => 10,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => false,
            'points_earned' => 0,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 6,
            'question_id' => 4,
            'selected_option_id' => 13,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 6,
            'question_id' => 5,
            'selected_option_id' => null,
            'selected_options' => [17, 18, 19, 22],
            'answer_text' => null,
            'is_correct' => false,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 6,
            'question_id' => 6,
            'selected_option_id' => 23,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 5,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 6,
            'question_id' => 8,
            'selected_option_id' => 26,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => false,
            'points_earned' => 0,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 6,
            'question_id' => 10,
            'selected_option_id' => 29,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        // Marie (attempt 3) - Exam 2 (Marketing) - failed
        UserAnswer::query()->create([
            'exam_attempt_id' => 3,
            'question_id' => 11,
            'selected_option_id' => 33,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 3,
            'question_id' => 12,
            'selected_option_id' => 37,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => true,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 3,
            'question_id' => 13,
            'selected_option_id' => null,
            'selected_options' => [41, 42, 45],
            'answer_text' => null,
            'is_correct' => false,
            'points_earned' => 10,
        ]);

        UserAnswer::query()->create([
            'exam_attempt_id' => 3,
            'question_id' => 14,
            'selected_option_id' => 47,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => false,
            'points_earned' => 0,
        ]);
    }
}
