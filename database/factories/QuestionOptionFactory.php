<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionOption>
 */
final class QuestionOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'option_text' => $this->faker->sentence(6),
            'is_correct' => false,
            'order_position' => $this->faker->numberBetween(1, 10),
            'image' => $this->faker->optional(0.05)->imageUrl(),
        ];
    }

    /**
     * Option correcte
     */
    public function correct(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
        ]);
    }

    /**
     * Option incorrecte
     */
    public function incorrect(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => false,
        ]);
    }

    /**
     * Option avec image
     */
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => $this->faker->imageUrl(400, 300, 'education'),
        ]);
    }
}
