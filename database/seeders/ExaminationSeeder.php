<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Examination;
use Illuminate\Database\Seeder;

final class ExaminationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Examination::factory()
            ->count(10)
            ->create();
    }
}
