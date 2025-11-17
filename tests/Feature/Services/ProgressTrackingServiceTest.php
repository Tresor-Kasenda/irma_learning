<?php

declare(strict_types=1);

use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Section;
use App\Models\User;
use App\Services\ProgressTrackingService;

beforeEach(function () {
    $this->service = new ProgressTrackingService();
    $this->user = User::factory()->create();
});

test('it can mark a chapter as in progress', function () {
    $formation = Formation::factory()->create();
    $module = Module::factory()->for($formation)->create();
    $section = Section::factory()->for($module)->create();
    $chapter = Chapter::factory()->for($section)->create();

    $progress = $this->service->markChapterAsInProgress($this->user, $chapter);

    expect($progress)->toBeInstanceOf(App\Models\UserProgress::class)
        ->and($progress->user_id)->toBe($this->user->id)
        ->and($progress->trackable_id)->toBe($chapter->id)
        ->and($progress->trackable_type)->toBe(Chapter::class)
        ->and($progress->status->value)->toBe('in_progress')
        ->and($progress->started_at)->not->toBeNull();
});

test('it can mark a chapter as completed', function () {
    $formation = Formation::factory()->create();
    $module = Module::factory()->for($formation)->create();
    $section = Section::factory()->for($module)->create();
    $chapter = Chapter::factory()->for($section)->create(['duration_minutes' => 30]);

    $progress = $this->service->markChapterAsCompleted($this->user, $chapter);

    expect($progress->status->value)->toBe('completed')
        ->and((float) $progress->progress_percentage)->toBe(100.0)
        ->and($progress->completed_at)->not->toBeNull()
        ->and($progress->time_spent)->toBe(30 * 60);
});

test('it can get completed chapters', function () {
    $formation = Formation::factory()->create();
    $module = Module::factory()->for($formation)->create();
    $section = Section::factory()->for($module)->create();

    $chapter1 = Chapter::factory()->for($section)->create();
    $chapter2 = Chapter::factory()->for($section)->create();
    $chapter3 = Chapter::factory()->for($section)->create();

    $this->service->markChapterAsCompleted($this->user, $chapter1);
    $this->service->markChapterAsCompleted($this->user, $chapter2);
    $this->service->markChapterAsInProgress($this->user, $chapter3);

    $completed = $this->service->getCompletedChapters(
        $this->user,
        [$chapter1->id, $chapter2->id, $chapter3->id]
    );

    expect($completed)->toHaveCount(2)
        ->and($completed->pluck('trackable_id')->toArray())->toContain($chapter1->id, $chapter2->id)
        ->and($completed->pluck('trackable_id')->toArray())->not->toContain($chapter3->id);
});

test('it can get last in progress chapter', function () {
    $formation = Formation::factory()->create();
    $module = Module::factory()->for($formation)->create();
    $section = Section::factory()->for($module)->create();

    $chapter1 = Chapter::factory()->for($section)->create();
    $chapter2 = Chapter::factory()->for($section)->create();

    $this->service->markChapterAsInProgress($this->user, $chapter1);
    sleep(1);
    $this->service->markChapterAsInProgress($this->user, $chapter2);

    $lastProgress = $this->service->getLastInProgressChapter(
        $this->user,
        [$chapter1->id, $chapter2->id]
    );

    expect($lastProgress)->not->toBeNull()
        ->and($lastProgress->trackable_id)->toBe($chapter2->id);
});

test('it calculates formation progress correctly', function () {
    $formation = Formation::factory()->create();
    $module = Module::factory()->for($formation)->create();
    $section = Section::factory()->for($module)->create();

    $chapter1 = Chapter::factory()->for($section)->create();
    $chapter2 = Chapter::factory()->for($section)->create();
    $chapter3 = Chapter::factory()->for($section)->create();
    $chapter4 = Chapter::factory()->for($section)->create();

    $chapterIds = [$chapter1->id, $chapter2->id, $chapter3->id, $chapter4->id];

    // No chapters completed
    $progress = $this->service->calculateFormationProgress($this->user, $chapterIds);
    expect($progress)->toBe(0.0);

    // 2 out of 4 completed (50%)
    $this->service->markChapterAsCompleted($this->user, $chapter1);
    $this->service->markChapterAsCompleted($this->user, $chapter2);

    $progress = $this->service->calculateFormationProgress($this->user, $chapterIds);
    expect($progress)->toBe(50.0);

    // All completed (100%)
    $this->service->markChapterAsCompleted($this->user, $chapter3);
    $this->service->markChapterAsCompleted($this->user, $chapter4);

    $progress = $this->service->calculateFormationProgress($this->user, $chapterIds);
    expect($progress)->toBe(100.0);
});

test('it updates enrollment progress correctly', function () {
    $formation = Formation::factory()->create();
    $module = Module::factory()->for($formation)->create();
    $section = Section::factory()->for($module)->create();

    $chapter1 = Chapter::factory()->for($section)->create();
    $chapter2 = Chapter::factory()->for($section)->create();

    $enrollment = Enrollment::factory()->for($this->user)->for($formation)->create([
        'status' => App\Enums\EnrollmentStatusEnum::Active,
        'payment_status' => App\Enums\EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 0,
    ]);

    $chapterIds = [$chapter1->id, $chapter2->id];

    // Complete one chapter (50%)
    $this->service->markChapterAsCompleted($this->user, $chapter1);
    $this->service->updateEnrollmentProgress($enrollment, $chapterIds);

    $enrollment->refresh();
    expect((float) $enrollment->progress_percentage)->toBe(50.0)
        ->and($enrollment->status->value)->toBe('active')
        ->and($enrollment->completion_date)->toBeNull();

    // Complete all chapters (100%)
    $this->service->markChapterAsCompleted($this->user, $chapter2);
    $this->service->updateEnrollmentProgress($enrollment, $chapterIds);

    $enrollment->refresh();
    expect((float) $enrollment->progress_percentage)->toBe(100.0)
        ->and($enrollment->status->value)->toBe('completed')
        ->and($enrollment->completion_date)->not->toBeNull();
});

test('it returns correct user progress stats', function () {
    $formation1 = Formation::factory()->create();
    $formation2 = Formation::factory()->create();

    Enrollment::factory()->for($this->user)->for($formation1)->create([
        'status' => App\Enums\EnrollmentStatusEnum::Active,
        'payment_status' => App\Enums\EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 50,
    ]);

    Enrollment::factory()->for($this->user)->for($formation2)->create([
        'status' => App\Enums\EnrollmentStatusEnum::Completed,
        'payment_status' => App\Enums\EnrollmentPaymentEnum::PAID,
        'progress_percentage' => 100,
    ]);

    $stats = $this->service->getUserProgressStats($this->user);

    expect($stats)->toBeArray()
        ->and($stats['totalEnrollments'])->toBe(2)
        ->and($stats['activeEnrollments'])->toBe(1)
        ->and($stats['completedEnrollments'])->toBe(1)
        ->and($stats['averageProgress'])->toBe(75)
        ->and($stats)->toHaveKeys([
            'totalEnrollments',
            'activeEnrollments',
            'completedEnrollments',
            'averageProgress',
            'totalTimeSpent',
            'certificatesEarned',
        ]);
});

test('it handles empty chapter list gracefully', function () {
    $progress = $this->service->calculateFormationProgress($this->user, []);

    expect($progress)->toBe(0.0);
});

test('it updates progress idempotently', function () {
    $formation = Formation::factory()->create();
    $module = Module::factory()->for($formation)->create();
    $section = Section::factory()->for($module)->create();
    $chapter = Chapter::factory()->for($section)->create();

    // Mark as in progress twice
    $progress1 = $this->service->markChapterAsInProgress($this->user, $chapter);
    $progress2 = $this->service->markChapterAsInProgress($this->user, $chapter);

    expect($progress1->id)->toBe($progress2->id)
        ->and($progress1->started_at)->toEqual($progress2->started_at);

    // Mark as completed twice
    $progress3 = $this->service->markChapterAsCompleted($this->user, $chapter);
    $progress4 = $this->service->markChapterAsCompleted($this->user, $chapter);

    expect($progress3->id)->toBe($progress4->id)
        ->and($progress3->completed_at)->toEqual($progress4->completed_at);
});
