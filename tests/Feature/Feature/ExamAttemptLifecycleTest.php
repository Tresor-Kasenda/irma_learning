<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\ExamAttemptEnum;
use App\Enums\UserRoleEnum;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Formation;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Section;
use App\Models\User;

function createLifecycleExam(Section $section, array $attributes = []): Exam
{
    $exam = Exam::factory()->for($section, 'examable')->create([
        'duration_minutes' => 30,
        'max_attempts' => 2,
        'is_active' => true,
        ...$attributes,
    ]);
    $question = Question::factory()->for($exam)->create();
    QuestionOption::factory()->for($question)->correct()->create();
    QuestionOption::factory()->for($question)->incorrect()->create();

    return $exam;
}

test('an in-progress attempt is resumed instead of creating a duplicate', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $section = Section::factory()->for($formation)->create();
    $exam = createLifecycleExam($section);
    Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::FREE,
    ]);

    $this->actingAs($user)->get(route('exam.take', $exam))->assertSuccessful();
    $attempt = ExamAttempt::query()->firstOrFail();

    $this->travel(5)->minutes();
    $this->actingAs($user)->get(route('exam.take', $exam))->assertSuccessful();

    expect(ExamAttempt::query()->count())->toBe(1)
        ->and($attempt->refresh()->last_activity_at)->not->toBeNull();
});

test('an expired attempt is closed server-side and consumes an attempt', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $section = Section::factory()->for($formation)->create();
    $exam = createLifecycleExam($section, ['max_attempts' => 1]);
    Enrollment::factory()->for($user)->for($formation)->create([
        'payment_status' => EnrollmentPaymentEnum::FREE,
    ]);
    $attempt = ExamAttempt::factory()->for($exam)->for($user)->create([
        'status' => ExamAttemptEnum::IN_PROGRESS,
        'started_at' => now()->subHour(),
        'expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->get(route('exam.take', $exam))
        ->assertRedirect();

    expect($attempt->refresh()->status)->toBe(ExamAttemptEnum::EXPIRED)
        ->and(ExamAttempt::query()->count())->toBe(1);
});

test('an admin can reopen an expired attempt without creating another attempt', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $user = User::factory()->create();
    $section = Section::factory()->for(Formation::factory())->create();
    $exam = createLifecycleExam($section, ['duration_minutes' => 45]);
    $attempt = ExamAttempt::factory()->for($exam)->for($user)->create([
        'status' => ExamAttemptEnum::EXPIRED,
        'expires_at' => now()->subMinute(),
        'completed_at' => now()->subMinute(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.attempts.reopen', $attempt))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($attempt->refresh())
        ->status->toBe(ExamAttemptEnum::IN_PROGRESS)
        ->reopened_by->toBe($admin->id)
        ->reopen_count->toBe(1);
    expect($attempt->expires_at->greaterThan(now()->addMinutes(44)))->toBeTrue()
        ->and(ExamAttempt::query()->count())->toBe(1);
});
