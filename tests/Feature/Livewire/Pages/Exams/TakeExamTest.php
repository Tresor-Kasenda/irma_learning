<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\ExamAttemptEnum;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Formation;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * @return array{0: Formation, 1: Exam}
 */
function createAccessibleFinalExam(User $user, array $examAttributes = []): array
{
    $formation = Formation::factory()->create([
        'is_certifying' => true,
        'price' => 0,
    ]);

    Enrollment::factory()->for($user)->for($formation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
    ]);

    $exam = Exam::factory()
        ->forFormation($formation)
        ->active()
        ->create([
            'max_attempts' => 0,
            ...$examAttributes,
        ]);

    return [$formation, $exam];
}

test('user can access active exam', function () {
    $user = User::factory()->create();
    [$formation, $exam] = createAccessibleFinalExam($user);
    Question::factory()->for($exam)->singleChoice()->create();

    $this->actingAs($user)
        ->get(route('exam.take', $exam))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('formation.id', $formation->id)
            ->where('examContext.type', 'formation')
            ->etc());
});

test('user cannot access inactive exam', function () {
    $user = User::factory()->create();
    [$formation, $exam] = createAccessibleFinalExam($user, ['is_active' => false]);

    $this->actingAs($user)
        ->get(route('exam.take', $exam))
        ->assertRedirect(route('course.player', $formation->id))
        ->assertSessionHas('error', 'Cet examen n\'est pas disponible.');
});

test('exam creates attempt when user starts exam', function () {
    $user = User::factory()->create();
    [, $exam] = createAccessibleFinalExam($user);
    Question::factory()->for($exam)->singleChoice()->create();

    expect(ExamAttempt::count())->toBe(0);

    $this->actingAs($user)->get(route('exam.take', $exam));

    expect(ExamAttempt::count())->toBe(1);
    expect(ExamAttempt::first()->user_id)->toBe($user->id);
    expect(ExamAttempt::first()->exam_id)->toBe($exam->id);
    expect(ExamAttempt::first()->status)->toBe(ExamAttemptEnum::IN_PROGRESS);
});

test('user can answer single choice question', function () {
    $user = User::factory()->create();
    [, $exam] = createAccessibleFinalExam($user);
    $question = Question::factory()->for($exam)->singleChoice()->create();
    $correctOption = QuestionOption::factory()->for($question)->correct()->create();
    QuestionOption::factory()->for($question)->incorrect()->count(3)->create();

    $this->actingAs($user)->get(route('exam.take', $exam));

    $this->post(route('exam.save-answer', $exam), [
        'question_id' => $question->id,
        'answer' => $correctOption->id,
    ])->assertOk();

    $this->assertDatabaseHas('user_answers', [
        'question_id' => $question->id,
        'selected_option_id' => $correctOption->id,
    ]);
});

test('user can answer multiple choice question', function () {
    $user = User::factory()->create();
    [, $exam] = createAccessibleFinalExam($user);
    $question = Question::factory()->for($exam)->multipleChoice()->create();
    $correctOptions = QuestionOption::factory()->for($question)->correct()->count(2)->create();
    QuestionOption::factory()->for($question)->incorrect()->count(2)->create();

    $selectedOptionIds = $correctOptions->pluck('id')->toArray();

    $this->actingAs($user)->get(route('exam.take', $exam));

    $this->post(route('exam.save-answer', $exam), [
        'question_id' => $question->id,
        'answer' => $selectedOptionIds,
    ])->assertOk();

    $answer = App\Models\UserAnswer::where('question_id', $question->id)->first();
    expect($answer)->not->toBeNull();
    $storedOptions = is_array($answer->selected_options)
        ? $answer->selected_options
        : json_decode($answer->selected_options, true);
    expect($storedOptions)->toEqual($selectedOptionIds);
});

test('user can submit exam and get score', function () {
    $user = User::factory()->create();
    [, $exam] = createAccessibleFinalExam($user, [
        'show_results_immediately' => true,
        'passing_score' => 40,
    ]);

    $question1 = Question::factory()->for($exam)->singleChoice()->create(['points' => 10]);
    $correctOption1 = QuestionOption::factory()->for($question1)->correct()->create();
    QuestionOption::factory()->for($question1)->incorrect()->count(3)->create();

    $question2 = Question::factory()->for($exam)->singleChoice()->create(['points' => 10]);
    QuestionOption::factory()->for($question2)->correct()->create();
    $incorrectOption2 = QuestionOption::factory()->for($question2)->incorrect()->create();

    $this->actingAs($user)->get(route('exam.take', $exam));

    $this->post(route('exam.save-answer', $exam), [
        'question_id' => $question1->id,
        'answer' => $correctOption1->id,
    ]);

    $this->post(route('exam.save-answer', $exam), [
        'question_id' => $question2->id,
        'answer' => $incorrectOption2->id,
    ]);

    $this->post(route('exam.submit', $exam))
        ->assertRedirect();

    $attempt = ExamAttempt::first();
    expect($attempt->status)->toBe(ExamAttemptEnum::COMPLETED);
    expect($attempt->score)->toBe(10);
    expect((float) $attempt->percentage)->toBe(50.0);
});

test('timer initializes correctly with duration', function () {
    $user = User::factory()->create();
    [, $exam] = createAccessibleFinalExam($user, ['duration_minutes' => 60]);
    Question::factory()->for($exam)->singleChoice()->create();

    $this->actingAs($user)
        ->get(route('exam.take', $exam))
        ->assertSuccessful();
});

test('questions are randomized when randomize is enabled', function () {
    $user = User::factory()->create();
    [, $exam] = createAccessibleFinalExam($user, ['randomize_questions' => true]);
    Question::factory()->for($exam)->singleChoice()->count(5)->create();

    $this->actingAs($user)->get(route('exam.take', $exam));

    $attempt = ExamAttempt::first();
    expect($attempt->question_order)->not->toBeNull();
    expect($attempt->question_order)->toHaveCount(5);
});

test('user cannot exceed max attempts', function () {
    $user = User::factory()->create();
    [$formation, $exam] = createAccessibleFinalExam($user, ['max_attempts' => 1]);
    Question::factory()->for($exam)->singleChoice()->create();

    ExamAttempt::factory()->for($exam)->for($user)->create([
        'status' => ExamAttemptEnum::COMPLETED,
        'completed_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('exam.take', $exam))
        ->assertRedirect(route('course.player', $formation->id))
        ->assertSessionHas('error', 'Vous avez atteint le nombre maximum de tentatives pour cet examen.');
});
