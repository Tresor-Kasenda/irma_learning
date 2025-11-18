<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chapter>
 */
final class ChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(3, true),
            'content_type' => $this->faker->randomElement(['text', 'video', 'pdf']),
            'media_url' => $this->faker->optional()->url,
            'duration_minutes' => $this->faker->numberBetween(5, 60),
            'order_position' => $this->faker->numberBetween(1, 20),
            'is_free' => $this->faker->boolean(20),
            'is_active' => true,
            'description' => $this->faker->sentence(),
        ];
    }
}
