<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ExamSubmission;
use Illuminate\Database\Seeder;

final class ExamSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ExamSubmission::factory()
            ->count(10)
            ->create();
    }
}
