<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
final class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->word,
            'value' => $this->faker->sentence,
        ];
    }
}
