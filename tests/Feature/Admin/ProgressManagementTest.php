<?php

declare(strict_types=1);

use App\Enums\UserProgressEnum;
use App\Enums\UserRoleEnum;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
use Inertia\Testing\AssertableInertia as Assert;

test('an admin can list learner progress', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $formation = Formation::factory()->create();
    $section = Section::factory()->for($formation)->create();
    $chapter = Chapter::factory()->for($section)->create();

    UserProgress::factory()->create([
        'trackable_type' => Chapter::class,
        'trackable_id' => $chapter->id,
        'status' => UserProgressEnum::IN_PROGRESS,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.progress.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Progress/Index')
            ->has('progress.data', 1)
            ->where('progress.data.0.formation_title', $formation->title));
});

test('an admin can mark a progress entry as started', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $progress = UserProgress::factory()->create(['status' => UserProgressEnum::NOT_STARTED]);

    $this->actingAs($admin)
        ->post(route('admin.progress.mark-started', $progress))
        ->assertRedirect();

    expect($progress->refresh()->status)->toBe(UserProgressEnum::IN_PROGRESS);
});

test('an admin can mark a progress entry as completed', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $progress = UserProgress::factory()->create(['status' => UserProgressEnum::IN_PROGRESS]);

    $this->actingAs($admin)
        ->post(route('admin.progress.mark-completed', $progress))
        ->assertRedirect();

    expect($progress->refresh()->status)->toBe(UserProgressEnum::COMPLETED);
});

test('an admin can bulk mark progress entries as completed', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $entries = UserProgress::factory()->count(3)->create(['status' => UserProgressEnum::IN_PROGRESS]);

    $this->actingAs($admin)
        ->post(route('admin.progress.bulk-mark-completed'), ['ids' => $entries->pluck('id')->all()])
        ->assertRedirect();

    expect(UserProgress::query()->where('status', UserProgressEnum::COMPLETED)->count())->toBe(3);
});

test('a student cannot access the progress admin', function () {
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($student)
        ->get(route('admin.progress.index'))
        ->assertForbidden();
});
