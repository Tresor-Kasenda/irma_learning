<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
final class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'formation_id' => Formation::factory(),
            'status' => EnrollmentStatusEnum::ACTIVE->value,
            'payment_status' => EnrollmentPaymentEnum::PENDING->value,
            'amount_paid' => 0,
            'currency' => 'XAF',
            'enrollment_date' => now(),
            'progress_percentage' => 0,
        ];
    }
}
