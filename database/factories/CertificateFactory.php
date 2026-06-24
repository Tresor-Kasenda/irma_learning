<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Formation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
final class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'formation_id' => Formation::factory(),
            'final_score' => $this->faker->randomFloat(2, 60, 100),
            'issue_date' => now(),
            'expiry_date' => $this->faker->optional(0.3)->dateTimeBetween('+1 year', '+3 years'),
            'status' => 'active',
            'metadata' => ['duration_hours' => $this->faker->numberBetween(10, 100)],
        ];
    }
}
