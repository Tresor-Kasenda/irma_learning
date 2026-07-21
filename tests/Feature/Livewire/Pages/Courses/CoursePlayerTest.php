<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->formation = Formation::factory()->create();
    $this->section = Section::factory()->for($this->formation)->create();
    $this->chapter1 = Chapter::factory()->for($this->section)->create([
        'title' => 'Chapter 1',
        'order_position' => 1,
        'is_active' => true,
    ]);
    $this->chapter2 = Chapter::factory()->for($this->section)->create([
        'title' => 'Chapter 2',
        'order_position' => 2,
        'is_active' => true,
    ]);

    $this->enrollment = Enrollment::factory()->for($this->user)->for($this->formation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 0,
    ]);
});

test('it redirects if user is not enrolled', function () {
    $unenrolledUser = User::factory()->create();
    $this->actingAs($unenrolledUser);

    $formation = Formation::factory()->create();

    $this->get(route('course.player', $formation))
        ->assertRedirect();
});

test('it renders course player for enrolled user', function () {
    $this->get(route('course.player', $this->formation))
        ->assertSuccessful()
        ->assertSee($this->formation->title)
        ->assertSee($this->chapter1->title);
});

test('it renders course player for a completed enrollment', function () {
    $this->enrollment->update([
        'status' => EnrollmentStatusEnum::COMPLETED,
        'progress_percentage' => 100,
    ]);

    $this->get(route('course.player', $this->formation))
        ->assertSuccessful()
        ->assertSee($this->formation->title);
});

test('it loads specified chapter when chapterId provided', function () {
    $this->get(route('course.player', [
        'formation' => $this->formation->id,
        'chapterId' => $this->chapter2->id,
    ]))
        ->assertSuccessful()
        ->assertSee($this->chapter2->title);
});

test('it does not load a chapter from another formation', function () {
    $otherFormation = Formation::factory()->create();
    $otherSection = Section::factory()->for($otherFormation)->create();
    $otherChapter = Chapter::factory()->for($otherSection)->create([
        'title' => 'Outside chapter',
        'is_active' => true,
    ]);

    $this->get(route('course.player', [
        'formation' => $this->formation->id,
        'chapterId' => $otherChapter->id,
    ]))
        ->assertSuccessful()
        ->assertSee($this->chapter1->title)
        ->assertDontSee($otherChapter->title);
});

test('it loads last in progress chapter when available', function () {
    UserProgress::create([
        'user_id' => $this->user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $this->chapter2->id,
        'status' => UserProgressEnum::IN_PROGRESS,
        'started_at' => now(),
    ]);

    $this->get(route('course.player', $this->formation))
        ->assertSuccessful()
        ->assertSee($this->chapter2->title);
});

test('it marks chapter as in progress when accessed', function () {
    $this->get(route('course.player', $this->formation));

    $this->assertDatabaseHas('user_progress', [
        'user_id' => $this->user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $this->chapter1->id,
        'status' => UserProgressEnum::IN_PROGRESS->value,
    ]);
});

test('it can mark chapter as completed', function () {
    $this->post(route('course.chapter.complete', [
        'formation' => $this->formation->id,
        'chapter' => $this->chapter1->id,
    ]));

    $this->assertDatabaseHas('user_progress', [
        'user_id' => $this->user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $this->chapter1->id,
        'status' => UserProgressEnum::COMPLETED->value,
        'progress_percentage' => 100,
    ]);
});

test('it updates enrollment progress when chapter completed', function () {
    $this->post(route('course.chapter.complete', [
        'formation' => $this->formation->id,
        'chapter' => $this->chapter1->id,
    ]));

    $this->enrollment->refresh();

    expect((float) $this->enrollment->progress_percentage)->toBe(33.33);
});

test('it keeps enrollment active until the mandatory section exam is passed', function () {
    $this->post(route('course.chapter.complete', [
        'formation' => $this->formation->id,
        'chapter' => $this->chapter1->id,
    ]));

    $this->post(route('course.chapter.complete', [
        'formation' => $this->formation->id,
        'chapter' => $this->chapter2->id,
    ]));

    $this->enrollment->refresh();

    expect((float) $this->enrollment->progress_percentage)->toBe(66.67)
        ->and($this->enrollment->status->value)->toBe('active')
        ->and($this->enrollment->completion_date)->toBeNull();
});

test('it redirects to next chapter after completion', function () {
    $this->post(route('course.chapter.complete', [
        'formation' => $this->formation->id,
        'chapter' => $this->chapter1->id,
    ]))
        ->assertRedirect(route('course.player', [
            'formation' => $this->formation->id,
            'chapterId' => $this->chapter2->id,
        ]));
});

test('it can complete a chapter from a completed enrollment', function () {
    $this->enrollment->update([
        'status' => EnrollmentStatusEnum::COMPLETED,
    ]);

    $this->post(route('course.chapter.complete', [
        'formation' => $this->formation->id,
        'chapter' => $this->chapter1->id,
    ]))
        ->assertRedirect(route('course.player', [
            'formation' => $this->formation->id,
            'chapterId' => $this->chapter2->id,
        ]));

    $this->assertDatabaseHas('user_progress', [
        'user_id' => $this->user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $this->chapter1->id,
        'status' => UserProgressEnum::COMPLETED->value,
    ]);
});

test('it shows completed chapters correctly', function () {
    UserProgress::create([
        'user_id' => $this->user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $this->chapter1->id,
        'status' => UserProgressEnum::COMPLETED,
        'progress_percentage' => 100,
        'completed_at' => now(),
    ]);

    $this->get(route('course.player', $this->formation))
        ->assertSuccessful();
});

test('it shows pagination info in header', function () {
    $this->get(route('course.player', $this->formation))
        ->assertSuccessful()
        ->assertSee('Chapter 1');
});

test('it shows section title in sidebar', function () {
    $this->get(route('course.player', $this->formation))
        ->assertSuccessful()
        ->assertSee($this->section->title);
});
