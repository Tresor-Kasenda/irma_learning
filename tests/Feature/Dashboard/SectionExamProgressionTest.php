<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\ExamAttemptEnum;
use App\Enums\UserProgressEnum;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
use App\Services\CourseProgressionService;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array{0: Formation, 1: array<int, array{section: Section, chapter: Chapter, exam: Exam|null}>}
 */
function buildSectionedFormation(int $sections = 2, bool $withExams = true): array
{
    $formation = Formation::factory()->create();
    $built = [];

    for ($i = 1; $i <= $sections; $i++) {
        $section = Section::factory()->for($formation)->create([
            'order_position' => $i,
            'title' => "Section {$i}",
        ]);
        $chapter = Chapter::factory()->for($section)->create([
            'order_position' => 1,
            'is_active' => true,
        ]);
        $exam = $withExams
            ? Exam::factory()->forSection($section)->active()->create(['passing_score' => 70])
            : null;

        $built[] = compact('section', 'chapter', 'exam');
    }

    return [$formation, $built];
}

function enrolStudent(User $user, Formation $formation): Enrollment
{
    return Enrollment::factory()->for($user)->for($formation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 0,
    ]);
}

function completeChapter(User $user, Chapter $chapter): void
{
    UserProgress::create([
        'user_id' => $user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $chapter->id,
        'status' => UserProgressEnum::COMPLETED,
        'progress_percentage' => 100,
        'completed_at' => now(),
    ]);
}

function passExam(User $user, Exam $exam, float $percentage = 80): void
{
    ExamAttempt::factory()->for($exam)->for($user)->create([
        'status' => ExamAttemptEnum::COMPLETED,
        'percentage' => $percentage,
        'score' => $percentage,
        'max_score' => 100,
        'completed_at' => now(),
    ]);
}

function failExam(User $user, Exam $exam, float $percentage = 40): void
{
    ExamAttempt::factory()->for($exam)->for($user)->create([
        'status' => ExamAttemptEnum::FAILED,
        'percentage' => $percentage,
        'score' => $percentage,
        'max_score' => 100,
        'completed_at' => now(),
    ]);
}

test('a section stays locked until the previous section is fully completed', function () {
    [$formation, $built] = buildSectionedFormation();
    $user = User::factory()->create();
    enrolStudent($user, $formation);

    $service = app(CourseProgressionService::class);

    $states = $service->sectionStates($user, $formation);
    expect($states[0]['unlocked'])->toBeTrue()
        ->and($states[1]['unlocked'])->toBeFalse();

    completeChapter($user, $built[0]['chapter']);
    passExam($user, $built[0]['exam']);

    $states = $service->sectionStates($user, $formation);
    expect($states[1]['unlocked'])->toBeTrue();
});

test('the formation is complete only when every chapter is done and every section exam is passed', function () {
    [$formation, $built] = buildSectionedFormation();
    $user = User::factory()->create();
    enrolStudent($user, $formation);
    $service = app(CourseProgressionService::class);

    completeChapter($user, $built[0]['chapter']);
    completeChapter($user, $built[1]['chapter']);
    passExam($user, $built[0]['exam']);

    expect($service->isFormationComplete($user, $formation))->toBeFalse();

    passExam($user, $built[1]['exam']);

    expect($service->isFormationComplete($user, $formation))->toBeTrue();
});

test('passing the final exam of a certifying formation issues a certificate and completes the enrollment', function () {
    [$formation, $built] = buildSectionedFormation();
    $formation->update(['is_certifying' => true]);
    $finalExam = Exam::factory()->forFormation($formation)->active()->create(['passing_score' => 70]);
    $user = User::factory()->create();
    $enrollment = enrolStudent($user, $formation);

    completeChapter($user, $built[0]['chapter']);
    completeChapter($user, $built[1]['chapter']);
    passExam($user, $built[0]['exam'], 80);
    passExam($user, $built[1]['exam'], 90);
    passExam($user, $finalExam, 88);

    $certificate = app(CourseProgressionService::class)->syncCompletion($user, $formation);

    expect($certificate)->not->toBeNull()
        ->and((float) $certificate->final_score)->toBe(88.0)
        ->and($certificate->status->value)->toBe('active');

    expect($enrollment->refresh()->status)->toBe(EnrollmentStatusEnum::COMPLETED)
        ->and($enrollment->completion_date)->not->toBeNull();
});

test('no certificate is issued while a section exam is still failed', function () {
    [$formation, $built] = buildSectionedFormation();
    $user = User::factory()->create();
    enrolStudent($user, $formation);

    completeChapter($user, $built[0]['chapter']);
    completeChapter($user, $built[1]['chapter']);
    passExam($user, $built[0]['exam']);
    failExam($user, $built[1]['exam']);

    $certificate = app(CourseProgressionService::class)->syncCompletion($user, $formation);

    expect($certificate)->toBeNull();
    expect(Certificate::where('user_id', $user->id)->exists())->toBeFalse();
});

test('the certificate is not issued twice', function () {
    [$formation, $built] = buildSectionedFormation();
    $formation->update(['is_certifying' => true]);
    $finalExam = Exam::factory()->forFormation($formation)->active()->create(['passing_score' => 70]);
    $user = User::factory()->create();
    enrolStudent($user, $formation);

    completeChapter($user, $built[0]['chapter']);
    completeChapter($user, $built[1]['chapter']);
    passExam($user, $built[0]['exam']);
    passExam($user, $built[1]['exam']);
    passExam($user, $finalExam);

    $service = app(CourseProgressionService::class);
    $service->syncCompletion($user, $formation);
    $service->syncCompletion($user, $formation);

    expect(Certificate::where('user_id', $user->id)->where('formation_id', $formation->id)->count())->toBe(1);
});

test('a formation without a section exam stays incomplete', function () {
    [$formation, $built] = buildSectionedFormation(sections: 1, withExams: false);
    $user = User::factory()->create();
    $enrollment = enrolStudent($user, $formation);

    completeChapter($user, $built[0]['chapter']);

    $certificate = app(CourseProgressionService::class)->syncCompletion($user, $formation);

    expect($certificate)->toBeNull();
    expect($enrollment->refresh()->status)->toBe(EnrollmentStatusEnum::ACTIVE);
});

test('a certifying formation stays incomplete until its final exam is passed', function () {
    [$formation, $built] = buildSectionedFormation(sections: 1);
    $formation->update(['is_certifying' => true]);
    $user = User::factory()->create();
    enrolStudent($user, $formation);

    completeChapter($user, $built[0]['chapter']);
    passExam($user, $built[0]['exam']);

    $service = app(CourseProgressionService::class);

    expect($service->areSectionsComplete($user, $formation))->toBeTrue()
        ->and($service->isFormationComplete($user, $formation))->toBeFalse()
        ->and($service->syncCompletion($user, $formation))->toBeNull();
});

test('the player exposes section locking state', function () {
    [$formation, $built] = buildSectionedFormation();
    $user = User::factory()->create();
    enrolStudent($user, $formation);
    $built[0]['chapter']->update([
        'content_type' => 'pdf',
        'content' => "# Support PDF\n\nContenu **lisible**.",
        'media_url' => 'chapters/support.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('course.player', $formation->id))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Learnings/StudentLearningPlay')
            ->where('htmlContent', fn (string $html): bool => str_contains($html, '<h1>Support PDF</h1>')
                && str_contains($html, '<strong>lisible</strong>')
                && ! str_contains($html, '<style>'))
            ->where('sections.0.unlocked', true)
            ->where('sections.1.unlocked', false)
            ->etc());
});

test('completing the last chapter of a section redirects to its exam', function () {
    [$formation, $built] = buildSectionedFormation();
    $user = User::factory()->create();
    enrolStudent($user, $formation);

    $this->actingAs($user)
        ->post(route('course.chapter.complete', [
            'formation' => $formation->id,
            'chapter' => $built[0]['chapter']->id,
        ]))
        ->assertRedirect(route('exam.take', $built[0]['exam']));
});

test('a section exam cannot be taken until all its chapters are completed', function () {
    [$formation, $built] = buildSectionedFormation(sections: 1);
    $user = User::factory()->create();
    enrolStudent($user, $formation);

    $this->actingAs($user)
        ->get(route('exam.take', $built[0]['exam']))
        ->assertRedirect(route('course.player', $formation->id))
        ->assertSessionHas('error');

    completeChapter($user, $built[0]['chapter']);

    $this->actingAs($user)
        ->get(route('exam.take', $built[0]['exam']))
        ->assertSuccessful();
});

test('the exam results expose the next section as the next step once passed', function () {
    [$formation, $built] = buildSectionedFormation();
    $user = User::factory()->create();
    enrolStudent($user, $formation);
    completeChapter($user, $built[0]['chapter']);

    $attempt = ExamAttempt::factory()->for($built[0]['exam'])->for($user)->create([
        'status' => ExamAttemptEnum::COMPLETED,
        'percentage' => 80,
        'score' => 80,
        'max_score' => 100,
        'completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('exam.results', $attempt))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('nextStep.type', 'next_section')
            ->where('nextStep.chapter_id', $built[1]['chapter']->id)
            ->etc());
});
