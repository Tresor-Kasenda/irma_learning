<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ExamAttemptEnum;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAttempt>
 */
final class ExamAttemptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'user_id' => User::factory(),
            'status' => ExamAttemptEnum::IN_PROGRESS,
            'started_at' => now(),
            'max_score' => $this->faker->numberBetween(50, 200),
        ];
    }

    /**
     * Tentative complétée
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $score = $this->faker->numberBetween(0, $attributes['max_score']);
            $percentage = $attributes['max_score'] > 0 ? ($score / $attributes['max_score']) * 100 : 0;

            return [
                'status' => ExamAttemptEnum::COMPLETED,
                'completed_at' => now(),
                'score' => $score,
                'percentage' => $percentage,
            ];
        });
    }

    /**
     * Tentative réussie
     */
    public function passed(): static
    {
        return $this->state(function (array $attributes) {
            $score = $this->faker->numberBetween((int) ($attributes['max_score'] * 0.7), $attributes['max_score']);
            $percentage = $attributes['max_score'] > 0 ? ($score / $attributes['max_score']) * 100 : 0;

            return [
                'status' => ExamAttemptEnum::COMPLETED,
                'completed_at' => now(),
                'score' => $score,
                'percentage' => $percentage,
            ];
        });
    }

    /**
     * Tentative échouée
     */
    public function failed(): static
    {
        return $this->state(function (array $attributes) {
            $score = $this->faker->numberBetween(0, (int) ($attributes['max_score'] * 0.5));
            $percentage = $attributes['max_score'] > 0 ? ($score / $attributes['max_score']) * 100 : 0;

            return [
                'status' => ExamAttemptEnum::COMPLETED,
                'completed_at' => now(),
                'score' => $score,
                'percentage' => $percentage,
            ];
        });
    }

    /**
     * Tentative en cours
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExamAttemptEnum::IN_PROGRESS,
            'completed_at' => null,
            'score' => null,
            'percentage' => null,
        ]);
    }
}
