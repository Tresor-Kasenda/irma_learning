<?php

declare(strict_types=1);

use App\Enums\UserProgressEnum;
use App\Enums\UserRoleEnum;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
use Database\Seeders\EnrollmentSeeder;
use Database\Seeders\UserProgressSeeder;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\seed;

/**
 * Build the minimum catalog the seeders need (two active formations, a section
 * and an active chapter) plus a brand-new student with no data, then run the
 * enrollment and progress seeders against it.
 */
function seedStudentWithoutData(): User
{
    $formation = Formation::factory()->create(['is_active' => true]);
    Formation::factory()->create(['is_active' => true]);

    $section = Section::factory()->for($formation)->create();
    Chapter::factory()->for($section)->create(['is_active' => true]);

    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    seed(EnrollmentSeeder::class);
    seed(UserProgressSeeder::class);

    return $student;
}

test('seeders enroll a brand-new student and seed in-progress data', function () {
    $student = seedStudentWithoutData();

    expect($student->enrollments()->count())->toBeGreaterThanOrEqual(1)
        ->and($student->progress()->count())->toBeGreaterThanOrEqual(1)
        ->and(
            UserProgress::query()
                ->where('user_id', $student->id)
                ->where('trackable_type', Chapter::class)
                ->where('status', UserProgressEnum::IN_PROGRESS->value)
                ->exists()
        )->toBeTrue();
});

test('dashboard exposes enrollments and continue watching after seeding', function () {
    $student = seedStudentWithoutData();

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Index')
            ->where('myEnrollments', fn ($enrollments) => count($enrollments) >= 1)
            ->has('continueWatching.id')
            ->etc());
});

test('learnings page lists formations after seeding', function () {
    $student = seedStudentWithoutData();

    $this->actingAs($student)
        ->get(route('student.learnings'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Formations/Index')
            ->where('formations.total', fn ($total) => $total >= 1)
            ->etc());
});

test('re-running the seeders is idempotent', function () {
    $student = seedStudentWithoutData();

    $countAfterFirstRun = $student->enrollments()->count();

    seed(EnrollmentSeeder::class);
    seed(UserProgressSeeder::class);

    expect($student->enrollments()->count())->toBe($countAfterFirstRun);
});
