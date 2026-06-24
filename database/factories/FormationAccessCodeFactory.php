<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Formation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormationAccessCode>
 */
final class FormationAccessCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'formation_id' => Formation::factory(),
            'code' => mb_strtoupper($this->faker->bothify('ACC-####-????')),
            'is_used' => false,
            'expires_at' => $this->faker->optional(0.7)->dateTimeBetween('+1 month', '+6 months'),
        ];
    }

    public function used(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
            'is_used' => true,
            'used_at' => now(),
        ]);
    }
}
