<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Examination;
use App\Models\MasterClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Examination>
 */
final class ExaminationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chapter_id' => Chapter::factory(),
            'master_class_id' => MasterClass::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'passing_score' => $this->faker->numberBetween(1, 100),
            'duration' => $this->faker->numberBetween(1, 60),
            'path' => $this->faker->imageUrl,
            'deadline' => $this->faker->dateTime,
        ];
    }
}
