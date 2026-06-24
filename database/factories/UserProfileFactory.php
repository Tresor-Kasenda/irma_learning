<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
final class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bio' => $this->faker->paragraph,
            'profession' => $this->faker->jobTitle,
            'linkedin' => 'https://linkedin.com/in/'.$this->faker->userName,
            'website' => $this->faker->optional(0.3)->url,
            'birth_date' => $this->faker->date('Y-m-d', '2000-01-01'),
            'country' => $this->faker->country,
            'city' => $this->faker->city,
            'preferences' => ['notifications' => true, 'language' => 'fr'],
        ];
    }
}
