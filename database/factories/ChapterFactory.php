<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\MasterClass;
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
            'master_class_id' => MasterClass::factory(),
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'points' => $this->faker->numberBetween(1, 20),
            'order_sequence' => $this->faker->numberBetween(1, 10),
            'description' => $this->faker->paragraph,
            'path' => $this->faker->url,
        ];
    }
}
