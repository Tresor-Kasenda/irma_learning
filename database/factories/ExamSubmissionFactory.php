<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Examination;
use App\Models\ExamSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamSubmission>
 */
final class ExamSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'chapter_id' => Chapter::factory(),
            'examination_id' => Examination::factory(),
            'file_path' => fake()->file('file_path', 'public/exam_submissions', false),
            'submitted_at' => fake()->dateTime(),
        ];
    }
}
