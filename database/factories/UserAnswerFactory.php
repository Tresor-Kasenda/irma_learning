<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\UserAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAnswer>
 */
final class UserAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_attempt_id' => ExamAttempt::factory(),
            'question_id' => Question::factory(),
            'selected_option_id' => null,
            'selected_options' => null,
            'answer_text' => null,
            'is_correct' => null,
            'points_earned' => 0,
        ];
    }

    public function singleChoice(int $optionId, bool $isCorrect = false): static
    {
        return $this->state(fn (array $attributes) => [
            'selected_option_id' => $optionId,
            'is_correct' => $isCorrect,
            'points_earned' => 0,
        ]);
    }

    public function multipleChoice(array $optionIds, bool $isCorrect = false, int $pointsEarned = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'selected_options' => $optionIds,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
        ]);
    }

    public function text(string $answer): static
    {
        return $this->state(fn (array $attributes) => [
            'answer_text' => $answer,
            'is_correct' => null,
            'points_earned' => 0,
        ]);
    }
}
