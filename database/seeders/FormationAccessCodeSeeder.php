<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FormationAccessCode;
use Illuminate\Database\Seeder;

final class FormationAccessCodeSeeder extends Seeder
{
    public function run(): void
    {
        FormationAccessCode::query()->create([
            'formation_id' => 1,
            'code' => 'ACC-FS-2026-001',
            'is_used' => false,
            'expires_at' => now()->addMonths(6),
        ]);

        FormationAccessCode::query()->create([
            'formation_id' => 1,
            'code' => 'ACC-FS-2026-002',
            'user_id' => 5,
            'is_used' => true,
            'used_at' => now()->subDays(5),
            'expires_at' => now()->addMonths(6),
        ]);

        FormationAccessCode::query()->create([
            'formation_id' => 2,
            'code' => 'ACC-MK-2026-001',
            'is_used' => false,
            'expires_at' => now()->addMonths(3),
        ]);

        FormationAccessCode::query()->create([
            'formation_id' => 3,
            'code' => 'ACC-DS-2026-001',
            'is_used' => false,
            'expires_at' => now()->addYear(),
        ]);
    }
}
