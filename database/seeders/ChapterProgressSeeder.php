<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ChapterProgress;
use Illuminate\Database\Seeder;

final class ChapterProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChapterProgress::factory()
            ->count(10)
            ->create();
    }
}
