<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuestionTypeEnum;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
final class QuestionFactory extends Factory
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
            'question_text' => $this->faker->sentence().'?',
            'question_type' => QuestionTypeEnum::SINGLE_CHOICE,
            'points' => $this->faker->randomElement([1, 2, 5, 10]),
            'order_position' => $this->faker->numberBetween(1, 20),
            'explanation' => $this->faker->optional(0.6)->paragraph(),
            'image' => $this->faker->optional(0.1)->imageUrl(),
            'is_required' => true,
        ];
    }

    /**
     * Question à choix unique
     */
    public function singleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::SINGLE_CHOICE,
        ]);
    }

    /**
     * Question à choix multiple
     */
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::MULTIPLE_CHOICE,
        ]);
    }

    /**
     * Question Vrai/Faux
     */
    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::TRUE_FALSE,
        ]);
    }

    /**
     * Question à réponse courte
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::TEXT,
        ]);
    }

    /**
     * Question de type essai
     */
    public function essay(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => QuestionTypeEnum::ESSAY,
            'points' => $this->faker->randomElement([5, 10, 15, 20]),
        ]);
    }

    /**
     * Question optionnelle
     */
    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => false,
        ]);
    }

    /**
     * Question avec image
     */
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => $this->faker->imageUrl(800, 600, 'education'),
        ]);
    }
}
