<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\VerificationCodeStatusEnum;
use App\Enums\VerificationCodeTypeEnum;
use App\Models\VerificationCode;
use Illuminate\Database\Seeder;

final class VerificationCodeSeeder extends Seeder
{
    public function run(): void
    {
        VerificationCode::withoutEvents(function () {
            VerificationCode::query()->create([
                'code' => 'ENR26A01',
                'user_id' => 5,
                'formation_id' => 1,
                'type' => VerificationCodeTypeEnum::Enrollment->value,
                'status' => VerificationCodeStatusEnum::Used->value,
                'used_at' => now()->subDays(5),
                'expires_at' => now()->addDays(19),
            ]);

            VerificationCode::query()->create([
                'code' => 'ENR26A02',
                'user_id' => 6,
                'formation_id' => 3,
                'type' => VerificationCodeTypeEnum::Enrollment->value,
                'status' => VerificationCodeStatusEnum::Used->value,
                'used_at' => now(),
                'expires_at' => now()->addDays(24),
            ]);

            VerificationCode::query()->create([
                'code' => 'ENR26A03',
                'user_id' => 8,
                'formation_id' => 3,
                'type' => VerificationCodeTypeEnum::Enrollment->value,
                'status' => VerificationCodeStatusEnum::Pending->value,
                'expires_at' => now()->addHours(24),
            ]);

            VerificationCode::query()->create([
                'code' => 'PWDRST01',
                'user_id' => 6,
                'formation_id' => 1,
                'type' => VerificationCodeTypeEnum::PasswordReset->value,
                'status' => VerificationCodeStatusEnum::Expired->value,
                'expires_at' => now()->subDays(2),
            ]);
        });
    }
}
