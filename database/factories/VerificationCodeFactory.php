<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VerificationCodeStatusEnum;
use App\Enums\VerificationCodeTypeEnum;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VerificationCode>
 */
final class VerificationCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => mb_strtoupper($this->faker->bothify('??####??')),
            'user_id' => User::factory(),
            'formation_id' => Formation::factory(),
            'type' => VerificationCodeTypeEnum::Enrollment->value,
            'status' => VerificationCodeStatusEnum::Pending->value,
            'expires_at' => now()->addHours(24),
        ];
    }
}
