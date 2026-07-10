<?php

declare(strict_types=1);

use App\Enums\ExamAttemptEnum;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders successfully', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()->forFormation($formation)->active()->create();

    $attempt = ExamAttempt::factory()
        ->for($exam)
        ->for($user)
        ->create([
            'status' => ExamAttemptEnum::COMPLETED,
            'completed_at' => now(),
            'score' => 8,
            'max_score' => 10,
            'percentage' => 80,
        ]);

    $this->actingAs($user)
        ->get(route('exam.results', $attempt))
        ->assertSuccessful();
});

it('returns chapter context after a chapter exam', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $section = Section::factory()->for($formation)->create();
    $chapter = Chapter::factory()->for($section)->create();
    $exam = Exam::factory()->forChapter($chapter)->active()->create();

    $attempt = ExamAttempt::factory()
        ->for($exam)
        ->for($user)
        ->create([
            'status' => ExamAttemptEnum::COMPLETED,
            'completed_at' => now(),
            'score' => 8,
            'max_score' => 10,
            'percentage' => 80,
        ]);

    $this->actingAs($user)
        ->get(route('exam.results', $attempt))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('formation.id', $formation->id)
            ->where('examContext.type', 'chapter')
            ->where('courseCompletion.formation_id', $formation->id)
            ->where('courseCompletion.chapter_id', $chapter->id)
            ->where('courseCompletion.chapter_title', $chapter->title)
            ->etc());
});
