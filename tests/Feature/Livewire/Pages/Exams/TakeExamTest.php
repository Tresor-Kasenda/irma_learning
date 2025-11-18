<?php

declare(strict_types=1);

use App\Enums\ExamAttemptEnum;
use App\Livewire\Pages\Exams\TakeExam;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Formation;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use Livewire\Livewire;

test('user can access active exam', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()->forFormation($formation)->active()->create();

    $this->actingAs($user);

    Livewire::test(TakeExam::class, ['exam' => $exam])
        ->assertOk();
});

test('user cannot access inactive exam', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()->forFormation($formation)->inactive()->create();

    $this->actingAs($user);

    Livewire::test(TakeExam::class, ['exam' => $exam])
        ->assertForbidden();
})->throws(Symfony\Component\HttpKernel\Exception\HttpException::class);

test('exam creates attempt when user starts exam', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()->forFormation($formation)->active()->create();

    $this->actingAs($user);

    expect(ExamAttempt::count())->toBe(0);

    Livewire::test(TakeExam::class, ['exam' => $exam]);

    expect(ExamAttempt::count())->toBe(1);
    expect(ExamAttempt::first()->user_id)->toBe($user->id);
    expect(ExamAttempt::first()->exam_id)->toBe($exam->id);
    expect(ExamAttempt::first()->status)->toBe(ExamAttemptEnum::IN_PROGRESS);
});

test('user can answer single choice question', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()->forFormation($formation)->active()->create();

    $question = Question::factory()
        ->for($exam)
        ->singleChoice()
        ->create();

    $correctOption = QuestionOption::factory()
        ->for($question)
        ->correct()
        ->create();

    QuestionOption::factory()
        ->for($question)
        ->incorrect()
        ->count(3)
        ->create();

    $this->actingAs($user);

    Livewire::test(TakeExam::class, ['exam' => $exam])
        ->set("answers.{$question->id}", $correctOption->id)
        ->call('nextQuestion')
        ->assertOk();

    $this->assertDatabaseHas('user_answers', [
        'question_id' => $question->id,
        'selected_option_id' => $correctOption->id,
    ]);
});

test('user can answer multiple choice question', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()->forFormation($formation)->active()->create();

    $question = Question::factory()
        ->for($exam)
        ->multipleChoice()
        ->create();

    $correctOptions = QuestionOption::factory()
        ->for($question)
        ->correct()
        ->count(2)
        ->create();

    QuestionOption::factory()
        ->for($question)
        ->incorrect()
        ->count(2)
        ->create();

    $selectedOptionIds = $correctOptions->pluck('id')->toArray();

    $this->actingAs($user);

    Livewire::test(TakeExam::class, ['exam' => $exam])
        ->set("answers.{$question->id}", $selectedOptionIds)
        ->call('nextQuestion')
        ->assertOk();

    $this->assertDatabaseHas('user_answers', [
        'question_id' => $question->id,
        'selected_options' => json_encode($selectedOptionIds),
    ]);
});

test('user can submit exam and get score', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()
        ->forFormation($formation)
        ->active()
        ->create(['show_results_immediately' => true]);

    $question1 = Question::factory()
        ->for($exam)
        ->singleChoice()
        ->create(['points' => 10]);

    $correctOption1 = QuestionOption::factory()
        ->for($question1)
        ->correct()
        ->create();

    QuestionOption::factory()
        ->for($question1)
        ->incorrect()
        ->count(3)
        ->create();

    $question2 = Question::factory()
        ->for($exam)
        ->singleChoice()
        ->create(['points' => 10]);

    QuestionOption::factory()
        ->for($question2)
        ->correct()
        ->create();

    $incorrectOption2 = QuestionOption::factory()
        ->for($question2)
        ->incorrect()
        ->create();

    $this->actingAs($user);

    Livewire::test(TakeExam::class, ['exam' => $exam])
        ->set("answers.{$question1->id}", $correctOption1->id)
        ->call('nextQuestion')
        ->set("answers.{$question2->id}", $incorrectOption2->id)
        ->call('submitExam')
        ->assertRedirect();

    $attempt = ExamAttempt::first();
    expect($attempt->status)->toBe(ExamAttemptEnum::COMPLETED);
    expect($attempt->score)->toBe(10);
    expect($attempt->percentage)->toBe(50.0);
});

test('timer initializes correctly with duration', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()
        ->forFormation($formation)
        ->active()
        ->create(['duration_minutes' => 60]);

    Question::factory()->for($exam)->singleChoice()->create();

    $this->actingAs($user);

    $component = Livewire::test(TakeExam::class, ['exam' => $exam]);

    expect($component->get('timeRemaining'))->toBeGreaterThan(0);
    expect($component->get('timeRemaining'))->toBeLessThanOrEqual(3600);
});

test('questions are randomized when randomize is enabled', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()
        ->forFormation($formation)
        ->active()
        ->create(['randomize_questions' => true]);

    $questions = Question::factory()
        ->for($exam)
        ->singleChoice()
        ->count(5)
        ->create();

    $this->actingAs($user);

    Livewire::test(TakeExam::class, ['exam' => $exam]);

    $attempt = ExamAttempt::first();
    expect($attempt->question_order)->not->toBeNull();
    expect($attempt->question_order)->toHaveCount(5);
});

test('user cannot exceed max attempts', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create();
    $exam = Exam::factory()
        ->forFormation($formation)
        ->active()
        ->create(['max_attempts' => 1]);

    Question::factory()->for($exam)->singleChoice()->create();

    // Create first completed attempt
    ExamAttempt::factory()
        ->for($exam)
        ->for($user)
        ->create([
            'status' => ExamAttemptEnum::COMPLETED,
            'completed_at' => now(),
        ]);

    $this->actingAs($user);

    Livewire::test(TakeExam::class, ['exam' => $exam])
        ->assertForbidden();
})->throws(Symfony\Component\HttpKernel\Exception\HttpException::class);
