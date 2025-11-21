<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Livewire\Pages\Courses\CoursePlayer;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create formation with structure
    $this->formation = Formation::factory()->create();
    $this->module = Module::factory()->for($this->formation)->create();
    $this->section = Section::factory()->for($this->module)->create();
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

    // Create enrollment
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

    Livewire::test(CoursePlayer::class, ['formation' => $formation])
        ->assertRedirect();
});

test('it renders course player for enrolled user', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->assertStatus(200)
        ->assertSee($this->formation->title)
        ->assertSee($this->chapter1->title)
        ->assertSee($this->chapter2->title);
});

test('it loads first chapter by default', function () {
    $component = Livewire::test(CoursePlayer::class, ['formation' => $this->formation]);

    expect($component->get('currentChapter')->id)->toBe($this->chapter1->id)
        ->and($component->get('currentChapterIndex'))->toBe(0);
});

test('it loads specified chapter when chapterId provided', function () {
    $component = Livewire::test(CoursePlayer::class, [
        'formation' => $this->formation,
        'chapterId' => $this->chapter2->id,
    ]);

    expect($component->get('currentChapter')->id)->toBe($this->chapter2->id)
        ->and($component->get('currentChapterIndex'))->toBe(1);
});

test('it loads last in progress chapter when available', function () {
    // Mark chapter2 as in progress
    UserProgress::create([
        'user_id' => $this->user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $this->chapter2->id,
        'status' => UserProgressEnum::IN_PROGRESS,
        'started_at' => now(),
    ]);

    $component = Livewire::test(CoursePlayer::class, ['formation' => $this->formation]);

    expect($component->get('currentChapter')->id)->toBe($this->chapter2->id)
        ->and($component->get('currentChapterIndex'))->toBe(1);
});

test('it can select a different chapter', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->call('selectChapter', $this->chapter2->id)
        ->assertSet('currentChapter.id', $this->chapter2->id)
        ->assertSet('currentChapterIndex', 1);
});

test('it marks chapter as in progress when accessed', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation]);

    $this->assertDatabaseHas('user_progress', [
        'user_id' => $this->user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $this->chapter1->id,
        'status' => UserProgressEnum::IN_PROGRESS->value,
    ]);
});

test('it can mark chapter as completed', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->call('markChapterAsCompleted');

    $this->assertDatabaseHas('user_progress', [
        'user_id' => $this->user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $this->chapter1->id,
        'status' => UserProgressEnum::COMPLETED->value,
        'progress_percentage' => 100,
    ]);
});

test('it updates enrollment progress when chapter completed', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->call('markChapterAsCompleted');

    $this->enrollment->refresh();

    expect((float)$this->enrollment->progress_percentage)->toBe(50.0); // 1 of 2 chapters
});

test('it marks enrollment as completed when all chapters done', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->call('markChapterAsCompleted')
        ->call('markChapterAsCompleted'); // Complete second chapter

    $this->enrollment->refresh();

    expect((float)$this->enrollment->progress_percentage)->toBe(100.0)
        ->and($this->enrollment->status->value)->toBe('completed')
        ->and($this->enrollment->completion_date)->not->toBeNull();
});

test('it can navigate to next chapter', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->assertSet('currentChapter.id', $this->chapter1->id)
        ->call('nextChapter')
        ->assertSet('currentChapter.id', $this->chapter2->id)
        ->assertSet('currentChapterIndex', 1);
});

test('it can navigate to previous chapter', function () {
    Livewire::test(CoursePlayer::class, [
        'formation' => $this->formation,
        'chapterId' => $this->chapter2->id,
    ])
        ->assertSet('currentChapter.id', $this->chapter2->id)
        ->call('previousChapter')
        ->assertSet('currentChapter.id', $this->chapter1->id)
        ->assertSet('currentChapterIndex', 0);
});

test('it cannot go to previous chapter from first chapter', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->assertSet('currentChapterIndex', 0)
        ->call('previousChapter')
        ->assertSet('currentChapterIndex', 0);
});

test('it cannot go to next chapter from last chapter', function () {
    Livewire::test(CoursePlayer::class, [
        'formation' => $this->formation,
        'chapterId' => $this->chapter2->id,
    ])
        ->assertSet('currentChapterIndex', 1)
        ->call('nextChapter')
        ->assertSet('currentChapterIndex', 1);
});

test('it shows completed chapters correctly', function () {
    // Mark chapter1 as completed
    UserProgress::create([
        'user_id' => $this->user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $this->chapter1->id,
        'status' => UserProgressEnum::COMPLETED,
        'progress_percentage' => 100,
        'completed_at' => now(),
    ]);

    $component = Livewire::test(CoursePlayer::class, ['formation' => $this->formation]);
    $completedChapters = $component->get('completedChapters');

    expect($completedChapters)->toContain($this->chapter1->id)
        ->and($completedChapters)->not->toContain($this->chapter2->id);
});

test('it advances to next chapter automatically after completion', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->assertSet('currentChapter.id', $this->chapter1->id)
        ->call('markChapterAsCompleted')
        ->assertSet('currentChapter.id', $this->chapter2->id);
});

test('it displays correct progress percentage in header', function () {
    $this->enrollment->update(['progress_percentage' => 50]);

    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->assertSee('50% complété');
});

test('it shows chapter count information', function () {
    Livewire::test(CoursePlayer::class, ['formation' => $this->formation])
        ->assertSee('Chapitre 1 sur 2');
});
