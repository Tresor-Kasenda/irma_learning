<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Chapter;
use Illuminate\Database\Seeder;

final class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Chapter::factory()
            ->count(10)
            ->create();
    }
}
