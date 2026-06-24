<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

final class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $certificates = [
            [
                'user_id' => 4,
                'formation_id' => 1,
                'final_score' => 85.00,
                'issue_date' => now()->subWeeks(2),
                'status' => 'active',
                'metadata' => [
                    'duration_hours' => 120,
                    'exam_attempts' => 1,
                    'instructor' => 'Jean Mbele',
                ],
            ],
            [
                'user_id' => 7,
                'formation_id' => 1,
                'final_score' => 92.00,
                'issue_date' => now()->subDays(10),
                'status' => 'active',
                'metadata' => [
                    'duration_hours' => 120,
                    'exam_attempts' => 1,
                    'instructor' => 'Jean Mbele',
                ],
            ],
        ];

        $existingUserIds = User::query()
            ->whereIn('id', array_unique(array_column($certificates, 'user_id')))
            ->pluck('id')
            ->all();

        $existingFormationIds = Formation::query()
            ->whereIn('id', array_unique(array_column($certificates, 'formation_id')))
            ->pluck('id')
            ->all();

        foreach ($certificates as $certificate) {
            if (! in_array($certificate['user_id'], $existingUserIds, true)) {
                continue;
            }

            if (! in_array($certificate['formation_id'], $existingFormationIds, true)) {
                continue;
            }

            Certificate::updateOrCreate(
                Arr::only($certificate, ['user_id', 'formation_id']),
                Arr::except($certificate, ['user_id', 'formation_id']),
            );
        }
    }
}
