<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriptionEnum;
use App\Models\MasterClass;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
final class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'master_class_id' => MasterClass::factory(),
            'status' => fake()->randomElement(SubscriptionEnum::cases()),
            'progress' => fake()->numberBetween(0, 100),
            'started_at' => fake()->dateTime(),
            'completed_at' => fake()->dateTime(),
        ];
    }
}
