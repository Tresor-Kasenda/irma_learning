<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\ExamAttemptEnum;
use App\Enums\UserProgressEnum;
use App\Enums\UserRoleEnum;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Formation;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
use App\Services\CourseProgressionService;

function createReadyAssessment(Section|Formation $examable, array $attributes = []): Exam
{
    $exam = Exam::factory()->for($examable, 'examable')->create([
        'is_active' => true,
        ...$attributes,
    ]);
    $question = Question::factory()->for($exam)->singleChoice()->create();
    QuestionOption::factory()->for($question)->correct()->create(['order_position' => 1]);
    QuestionOption::factory()->for($question)->incorrect()->create(['order_position' => 2]);

    return $exam;
}

test('a formation cannot be activated while a section assessment is missing', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $formation = Formation::factory()->create(['is_active' => false]);
    Section::factory()->for($formation)->create(['is_active' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.formations.toggle-active', $formation->id))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($formation->refresh()->is_active)->toBeFalse();
});

test('a formation cannot be activated with an invalid section assessment', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $formation = Formation::factory()->create(['is_active' => false]);
    $section = Section::factory()->for($formation)->create(['is_active' => true]);
    Chapter::factory()->for($section)->create(['is_active' => true]);
    $exam = Exam::factory()->for($section, 'examable')->create(['is_active' => true]);
    $question = Question::factory()->for($exam)->create();
    QuestionOption::factory()->for($question)->incorrect()->count(2)->create();

    $this->actingAs($admin)
        ->patch(route('admin.formations.toggle-active', $formation->id))
        ->assertSessionHas('error');

    expect($formation->refresh()->is_active)->toBeFalse();
});

test('a certifying formation requires a valid final exam before activation', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $formation = Formation::factory()->create(['is_active' => false, 'is_certifying' => true]);
    $section = Section::factory()->for($formation)->create(['is_active' => true]);
    Chapter::factory()->for($section)->create(['is_active' => true]);
    createReadyAssessment($section);

    $this->actingAs($admin)
        ->patch(route('admin.formations.toggle-active', $formation->id))
        ->assertSessionHas('error');

    createReadyAssessment($formation);

    $this->actingAs($admin)
        ->patch(route('admin.formations.toggle-active', $formation->id))
        ->assertSessionHas('success');

    expect($formation->refresh()->is_active)->toBeTrue();
});

test('a learner cannot complete a chapter from the next section before passing the previous exam', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $firstSection = Section::factory()->for($formation)->create(['order_position' => 1]);
    $secondSection = Section::factory()->for($formation)->create(['order_position' => 2]);
    $firstChapter = Chapter::factory()->for($firstSection)->create(['order_position' => 1, 'is_active' => true]);
    $secondChapter = Chapter::factory()->for($secondSection)->create(['order_position' => 1, 'is_active' => true]);
    $firstExam = createReadyAssessment($firstSection);
    createReadyAssessment($secondSection);
    Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::FREE,
    ]);

    $this->actingAs($user)
        ->post(route('course.chapter.complete', [$formation, $secondChapter]))
        ->assertForbidden();

    UserProgress::query()->create([
        'user_id' => $user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $firstChapter->id,
        'status' => UserProgressEnum::COMPLETED,
        'progress_percentage' => 100,
        'completed_at' => now(),
    ]);
    ExamAttempt::factory()->for($firstExam)->for($user)->create([
        'status' => ExamAttemptEnum::COMPLETED,
        'percentage' => 100,
        'score' => 1,
        'max_score' => 1,
        'completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('course.chapter.complete', [$formation, $secondChapter]))
        ->assertRedirect();

    expect(UserProgress::query()
        ->where('user_id', $user->id)
        ->where('trackable_type', Chapter::class)
        ->where('trackable_id', $secondChapter->id)
        ->where('status', UserProgressEnum::COMPLETED)
        ->exists())->toBeTrue();
});

test('a certifying formation is complete only after section and final assessments pass', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['is_certifying' => true]);
    $section = Section::factory()->for($formation)->create();
    $chapter = Chapter::factory()->for($section)->create();
    $sectionExam = createReadyAssessment($section);
    $finalExam = createReadyAssessment($formation);
    Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::FREE,
    ]);
    UserProgress::query()->create([
        'user_id' => $user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $chapter->id,
        'status' => UserProgressEnum::COMPLETED,
        'progress_percentage' => 100,
    ]);
    ExamAttempt::factory()->for($sectionExam)->for($user)->create([
        'status' => ExamAttemptEnum::COMPLETED,
        'percentage' => 100,
        'score' => 1,
        'max_score' => 1,
    ]);

    $progression = app(CourseProgressionService::class);
    expect($progression->isFormationComplete($user, $formation))->toBeFalse();

    ExamAttempt::factory()->for($finalExam)->for($user)->create([
        'status' => ExamAttemptEnum::COMPLETED,
        'percentage' => 100,
        'score' => 1,
        'max_score' => 1,
    ]);

    expect($progression->isFormationComplete($user, $formation))->toBeTrue()
        ->and($progression->syncCompletion($user, $formation))->not->toBeNull();
});
