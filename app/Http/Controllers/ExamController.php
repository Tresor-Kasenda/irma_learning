<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ExamAttemptEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\UserAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class ExamController extends Controller
{
    public function take(Exam $exam)
    {
        $user = auth()->user();

        if (! $exam->is_active) {
            abort(403, 'Cet examen n\'est pas disponible.');
        }
        if ($exam->available_from && now()->isBefore($exam->available_from)) {
            abort(403, 'Cet examen n\'est pas encore disponible.');
        }
        if ($exam->available_until && now()->isAfter($exam->available_until)) {
            abort(403, 'Cet examen n\'est plus disponible.');
        }
        if (! $exam->canUserAttempt($user)) {
            abort(403, 'Vous avez atteint le nombre maximum de tentatives pour cet examen.');
        }

        $attempt = ExamAttempt::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'user_id' => $user->id,
                'status' => ExamAttemptEnum::IN_PROGRESS,
            ],
            [
                'started_at' => now(),
                'max_score' => $exam->getTotalPoints(),
            ]
        );

        if ($exam->randomize_questions && ! $attempt->question_order) {
            $order = $exam->questions()->pluck('id')->shuffle()->values()->toArray();
            $attempt->update(['question_order' => $order]);
            $attempt->refresh();
        }

        $timeRemaining = null;
        if ($exam->duration_minutes) {
            $elapsed = now()->diffInSeconds($attempt->started_at);
            $totalSeconds = $exam->duration_minutes * 60;
            $timeRemaining = (int) max(0, $totalSeconds - $elapsed);
        }

        $questionsQuery = $exam->questions()->with('options');

        if ($attempt->question_order) {
            $questions = $questionsQuery->get()->sortBy(function ($question) use ($attempt) {
                return array_search($question->id, $attempt->question_order);
            })->values();
        } else {
            $questions = $questionsQuery->get();
        }

        $questions = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type->value,
                'points' => $question->points,
                'image' => $question->image,
                'explanation' => $question->explanation,
                'is_required' => $question->is_required,
                'options' => $question->options->map(fn ($option) => [
                    'id' => $option->id,
                    'option_text' => $option->option_text,
                    'image' => $option->image,
                ]),
            ];
        });

        $userAnswers = UserAnswer::with('question')->where('exam_attempt_id', $attempt->id)->get();
        $existingAnswers = [];

        foreach ($userAnswers as $userAnswer) {
            $questionId = $userAnswer->question_id;

            if ($userAnswer->question->question_type === QuestionTypeEnum::MULTIPLE_CHOICE) {
                $existingAnswers[$questionId] = is_array($userAnswer->selected_options)
                    ? $userAnswer->selected_options
                    : json_decode($userAnswer->selected_options ?? '[]', true);
            } elseif (in_array($userAnswer->question->question_type, [QuestionTypeEnum::SINGLE_CHOICE, QuestionTypeEnum::TRUE_FALSE])) {
                $existingAnswers[$questionId] = $userAnswer->selected_option_id;
            } elseif (in_array($userAnswer->question->question_type, [QuestionTypeEnum::TEXT, QuestionTypeEnum::ESSAY])) {
                $existingAnswers[$questionId] = $userAnswer->answer_text;
            }
        }

        return Inertia::render('Exams/Take', [
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'description' => $exam->description,
                'instructions' => $exam->instructions,
                'duration_minutes' => $exam->duration_minutes,
                'passing_score' => $exam->passing_score ?? 70,
                'examable_type' => $exam->examable_type,
                'show_results_immediately' => $exam->show_results_immediately,
            ],
            'questions' => $questions,
            'attempt' => [
                'id' => $attempt->id,
                'started_at' => $attempt->started_at,
                'status' => $attempt->status,
            ],
            'existingAnswers' => $existingAnswers,
            'timeRemaining' => $timeRemaining,
        ]);
    }

    public function saveAnswer(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'nullable',
        ]);

        $user = auth()->user();
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', ExamAttemptEnum::IN_PROGRESS)
            ->firstOrFail();

        $question = $exam->questions()->findOrFail($validated['question_id']);
        $answer = $validated['answer'];

        $updateData = [];

        if (in_array($question->question_type, [QuestionTypeEnum::SINGLE_CHOICE, QuestionTypeEnum::TRUE_FALSE])) {
            $updateData['selected_option_id'] = $answer;
            $updateData['selected_options'] = null;
            $updateData['answer_text'] = null;
        } elseif ($question->question_type === QuestionTypeEnum::MULTIPLE_CHOICE) {
            $updateData['selected_option_id'] = null;
            $updateData['selected_options'] = is_array($answer) ? $answer : [];
            $updateData['answer_text'] = null;
        } elseif (in_array($question->question_type, [QuestionTypeEnum::TEXT, QuestionTypeEnum::ESSAY])) {
            $updateData['selected_option_id'] = null;
            $updateData['selected_options'] = null;
            $updateData['answer_text'] = $answer;
        }

        UserAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            $updateData
        );

        return response()->json(['saved' => true]);
    }

    public function submit(Exam $exam)
    {
        $user = auth()->user();
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', ExamAttemptEnum::IN_PROGRESS)
            ->firstOrFail();

        $attempt->complete();

        if ($exam->show_results_immediately) {
            return redirect()->to(route('exam.results', $attempt));
        }

        if ($exam->examable_type === 'App\Models\Chapter') {
            $chapter = $exam->examable;
            $chapter->load('section.formation');

            if ($chapter->section && $chapter->section->formation) {
                return redirect()->route('course.player', [
                    'formation' => $chapter->section->formation->slug,
                    'chapterId' => $chapter->id,
                ])->with('success', 'Votre examen a été soumis avec succès!');
            }
        }

        return redirect()->route('dashboard')->with('success', 'Votre examen a été soumis avec succès!');
    }

    public function results(ExamAttempt $attempt)
    {
        Gate::authorize('view', $attempt);

        $attempt->load('exam.examable');

        $userAnswers = $attempt->userAnswers()
            ->with(['question.options', 'selectedOption'])
            ->get()
            ->sortBy(function ($answer) use ($attempt) {
                if ($attempt->question_order) {
                    return array_search($answer->question_id, $attempt->question_order);
                }

                return $answer->question->order_position;
            })
            ->values();

        $passed = (float) $attempt->percentage >= $attempt->exam->getPassingScore();
        $canRetry = ! $passed && $attempt->exam->canUserAttempt(auth()->user());

        $timeTaken = null;
        if ($attempt->started_at && $attempt->completed_at) {
            $timeTaken = $attempt->started_at->diffInMinutes($attempt->completed_at);
        }

        $courseCompletion = null;
        if ($attempt->exam->examable_type === 'App\Models\Chapter') {
            $chapter = $attempt->exam->examable;
            $chapter->loadMissing('section.formation');

            if ($chapter->section?->formation) {
                $courseCompletion = [
                    'formation_id' => $chapter->section->formation->id,
                    'chapter_id' => $chapter->id,
                    'formation_title' => $chapter->section->formation->title,
                    'chapter_title' => $chapter->title,
                ];
            }
        }

        return Inertia::render('Exams/Results', [
            'attempt' => [
                'id' => $attempt->id,
                'score' => $attempt->score,
                'max_score' => $attempt->max_score,
                'percentage' => (float) $attempt->percentage,
                'passed' => $passed,
                'completed_at' => $attempt->completed_at,
                'time_taken' => $timeTaken ?? $attempt->time_taken,
            ],
            'exam' => [
                'id' => $attempt->exam->id,
                'title' => $attempt->exam->title,
                'passing_score' => $attempt->exam->getPassingScore(),
            ],
            'userAnswers' => $userAnswers->map(function ($ua) {
                return [
                    'id' => $ua->id,
                    'question_id' => $ua->question_id,
                    'is_correct' => $ua->is_correct,
                    'points_earned' => $ua->points_earned,
                    'selected_option_id' => $ua->selected_option_id,
                    'selected_options' => $ua->selected_options,
                    'answer_text' => $ua->answer_text,
                    'question' => [
                        'id' => $ua->question->id,
                        'question_text' => $ua->question->question_text,
                        'question_type' => $ua->question->question_type->value,
                        'points' => $ua->question->points,
                        'explanation' => $ua->question->explanation,
                        'options' => $ua->question->options->map(fn ($opt) => [
                            'id' => $opt->id,
                            'option_text' => $opt->option_text,
                            'is_correct' => $opt->is_correct,
                            'image' => $opt->image,
                        ]),
                    ],
                    'selectedOption' => $ua->selectedOption ? [
                        'id' => $ua->selectedOption->id,
                        'option_text' => $ua->selectedOption->option_text,
                    ] : null,
                ];
            }),
            'canRetry' => $canRetry,
            'courseCompletion' => $courseCompletion,
        ]);
    }
}
