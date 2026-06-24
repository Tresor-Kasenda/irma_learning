<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserProgressEnum;
use App\Enums\UserRoleEnum;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

final class UserProgressSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCuratedProgress();
        $this->seedProgressForRemainingStudents();
    }

    /**
     * Progress records for the demo students (ids 4, 5, 7, 8). Guarded by user
     * existence and idempotent so the seeder is safe to re-run or run standalone.
     */
    private function seedCuratedProgress(): void
    {
        $curated = [
            ['user_id' => 4, 'trackable_type' => Formation::class, 'trackable_id' => 1, 'progress_percentage' => 100.00, 'time_spent' => 432000, 'status' => UserProgressEnum::COMPLETED->value, 'started_at' => now()->subMonths(3), 'completed_at' => now()->subWeeks(2)],
            ['user_id' => 4, 'trackable_type' => Section::class, 'trackable_id' => 1, 'progress_percentage' => 100.00, 'time_spent' => 10800, 'status' => UserProgressEnum::COMPLETED->value, 'started_at' => now()->subMonths(3), 'completed_at' => now()->subMonths(2)],
            ['user_id' => 4, 'trackable_type' => Section::class, 'trackable_id' => 2, 'progress_percentage' => 100.00, 'time_spent' => 21600, 'status' => UserProgressEnum::COMPLETED->value, 'started_at' => now()->subMonths(2), 'completed_at' => now()->subMonth()->subWeek()],
            ['user_id' => 4, 'trackable_type' => Chapter::class, 'trackable_id' => 1, 'progress_percentage' => 100.00, 'time_spent' => 2700, 'status' => UserProgressEnum::COMPLETED->value, 'started_at' => now()->subMonths(3), 'completed_at' => now()->subMonths(3)->addHours(2)],
            ['user_id' => 4, 'trackable_type' => Chapter::class, 'trackable_id' => 2, 'progress_percentage' => 100.00, 'time_spent' => 3600, 'status' => UserProgressEnum::COMPLETED->value, 'started_at' => now()->subMonths(3)->addDay(), 'completed_at' => now()->subMonths(3)->addDay()->addHours(2)],
            ['user_id' => 4, 'trackable_type' => Formation::class, 'trackable_id' => 2, 'progress_percentage' => 60.00, 'time_spent' => 72000, 'status' => UserProgressEnum::IN_PROGRESS->value, 'started_at' => now()->subMonth(), 'completed_at' => null],
            ['user_id' => 5, 'trackable_type' => Formation::class, 'trackable_id' => 2, 'progress_percentage' => 30.00, 'time_spent' => 36000, 'status' => UserProgressEnum::IN_PROGRESS->value, 'started_at' => now()->subWeeks(2), 'completed_at' => null],
            ['user_id' => 5, 'trackable_type' => Section::class, 'trackable_id' => 5, 'progress_percentage' => 70.00, 'time_spent' => 7200, 'status' => UserProgressEnum::IN_PROGRESS->value, 'started_at' => now()->subWeeks(2), 'completed_at' => null],
            ['user_id' => 7, 'trackable_type' => Formation::class, 'trackable_id' => 1, 'progress_percentage' => 80.00, 'time_spent' => 288000, 'status' => UserProgressEnum::IN_PROGRESS->value, 'started_at' => now()->subMonths(2), 'completed_at' => null],
            ['user_id' => 7, 'trackable_type' => Section::class, 'trackable_id' => 3, 'progress_percentage' => 60.00, 'time_spent' => 14400, 'status' => UserProgressEnum::IN_PROGRESS->value, 'started_at' => now()->subWeek(), 'completed_at' => null],
            ['user_id' => 8, 'trackable_type' => Formation::class, 'trackable_id' => 1, 'progress_percentage' => 50.00, 'time_spent' => 180000, 'status' => UserProgressEnum::IN_PROGRESS->value, 'started_at' => now()->subMonth(), 'completed_at' => null],
        ];

        if ($paulChapter = $this->firstActiveChapterForFormation(2)) {
            $curated[] = ['user_id' => 4, 'trackable_type' => Chapter::class, 'trackable_id' => $paulChapter->id, 'progress_percentage' => 40.00, 'time_spent' => 1800, 'status' => UserProgressEnum::IN_PROGRESS->value, 'started_at' => now()->subDays(3), 'completed_at' => null];
        }

        $existingUserIds = User::query()
            ->whereIn('id', array_unique(array_column($curated, 'user_id')))
            ->pluck('id')
            ->all();

        foreach ($curated as $progress) {
            if (! in_array($progress['user_id'], $existingUserIds, true)) {
                continue;
            }

            $trackableExists = $progress['trackable_type']::query()
                ->whereKey($progress['trackable_id'])
                ->exists();

            if (! $trackableExists) {
                continue;
            }

            UserProgress::updateOrCreate(
                Arr::only($progress, ['user_id', 'trackable_type', 'trackable_id']),
                Arr::except($progress, ['user_id', 'trackable_type', 'trackable_id']),
            );
        }
    }

    /**
     * Give enrolled students that have no progress yet a formation-level and a
     * chapter-level "in progress" record so the dashboard stats and the
     * "Continue watching" widget render for them.
     */
    private function seedProgressForRemainingStudents(): void
    {
        $students = User::query()
            ->where('role', UserRoleEnum::STUDENT->value)
            ->whereHas('enrollments')
            ->whereDoesntHave('progress')
            ->with('enrollments')
            ->get();

        foreach ($students as $student) {
            $formationId = $student->enrollments->first()->formation_id;

            UserProgress::updateOrCreate(
                ['user_id' => $student->id, 'trackable_type' => Formation::class, 'trackable_id' => $formationId],
                [
                    'progress_percentage' => 45.00,
                    'time_spent' => 54000,
                    'status' => UserProgressEnum::IN_PROGRESS->value,
                    'started_at' => now()->subWeeks(2),
                    'completed_at' => null,
                ],
            );

            $chapter = $this->firstActiveChapterForFormation($formationId);

            if ($chapter === null) {
                continue;
            }

            UserProgress::updateOrCreate(
                ['user_id' => $student->id, 'trackable_type' => Chapter::class, 'trackable_id' => $chapter->id],
                [
                    'progress_percentage' => 40.00,
                    'time_spent' => 1800,
                    'status' => UserProgressEnum::IN_PROGRESS->value,
                    'started_at' => now()->subDays(2),
                    'completed_at' => null,
                ],
            );
        }
    }

    private function firstActiveChapterForFormation(int $formationId): ?Chapter
    {
        return Chapter::query()
            ->whereHas('section', fn (Builder $query): Builder => $query->where('formation_id', $formationId))
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }
}
