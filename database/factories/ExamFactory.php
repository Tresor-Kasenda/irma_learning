<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
final class ExamFactory extends Factory
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
            'description' => $this->faker->paragraph(),
            'instructions' => $this->faker->paragraph(2),
            'duration_minutes' => $this->faker->randomElement([30, 45, 60, 90, 120]),
            'passing_score' => $this->faker->numberBetween(50, 80),
            'max_attempts' => $this->faker->randomElement([0, 1, 2, 3, 5]),
            'randomize_questions' => $this->faker->boolean(70),
            'show_results_immediately' => $this->faker->boolean(80),
            'is_active' => true,
            'available_from' => now()->subDays(7),
            'available_until' => $this->faker->optional(0.3)->dateTimeBetween('now', '+3 months'),
        ];
    }

    /**
     * Exam pour une Formation
     */
    public function forFormation(?Formation $formation = null): static
    {
        return $this->state(fn (array $attributes) => [
            'examable_type' => Formation::class,
            'examable_id' => $formation?->id ?? Formation::factory(),
        ]);
    }

    /**
     * Exam pour une Section
     */
    public function forSection(?Section $section = null): static
    {
        return $this->state(fn (array $attributes) => [
            'examable_type' => Section::class,
            'examable_id' => $section?->id ?? Section::factory(),
        ]);
    }

    /**
     * Exam pour un Chapter
     */
    public function forChapter(?Chapter $chapter = null): static
    {
        return $this->state(fn (array $attributes) => [
            'examable_type' => Chapter::class,
            'examable_id' => $chapter?->id ?? Chapter::factory(),
        ]);
    }

    /**
     * Exam actif
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'available_from' => now()->subDays(1),
            'available_until' => now()->addMonths(3),
        ]);
    }

    /**
     * Exam inactif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Exam avec tentatives illimitées
     */
    public function unlimitedAttempts(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_attempts' => 0,
        ]);
    }

    /**
     * Exam strict (une seule tentative, pas de résultats immédiats)
     */
    public function strict(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_attempts' => 1,
            'show_results_immediately' => false,
            'randomize_questions' => true,
        ]);
    }
}
