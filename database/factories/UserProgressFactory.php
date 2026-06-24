<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserProgressEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProgress>
 */
final class UserProgressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'trackable_type' => $this->faker->randomElement([
                \App\Models\Formation::class,
                \App\Models\Section::class,
                \App\Models\Chapter::class,
            ]),
            'trackable_id' => 1,
            'progress_percentage' => $this->faker->randomFloat(2, 0, 100),
            'time_spent' => $this->faker->numberBetween(0, 3600),
            'status' => $this->faker->randomElement(UserProgressEnum::cases()),
            'started_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 month'),
            'completed_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month'),
        ];
    }
}
