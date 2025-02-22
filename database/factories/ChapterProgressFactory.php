<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ChapterProgressEnum;
use App\Models\Chapter;
use App\Models\ChapterProgress;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChapterProgress>
 */
final class ChapterProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'chapter_id' => Chapter::factory(),
            'status' => $this->faker->randomElement(ChapterProgressEnum::cases()),
            'points_earned' => $this->faker->numberBetween(0, 100),
            'completed_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
