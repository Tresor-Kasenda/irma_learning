<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Enums\CertificateStatusEnum;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\ExamAttemptEnum;
use App\Enums\QuestionTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserAnswer;
use App\Services\CourseProgressionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class ExamController extends Controller
{
    public function take(Exam $exam, CourseProgressionService $progression)
    {
        $user = auth()->user();
        $formation = $this->resolveFormation($exam);

        if (! $exam->is_active) {
            return $this->redirectToLearning($user, $formation, $progression, 'Cet examen n\'est pas disponible.');
        }
        if ($exam->available_from && now()->isBefore($exam->available_from)) {
            return $this->redirectToLearning($user, $formation, $progression, 'Cet examen n\'est pas encore disponible.');
        }
        if ($exam->available_until && now()->isAfter($exam->available_until)) {
            return $this->redirectToLearning($user, $formation, $progression, 'Cet examen n\'est plus disponible.');
        }

        if ($exam->examable_type === Section::class) {
            $section = $exam->examable;

            if (! $formation || ! $this->isEnrolled($user, $formation)) {
                return $this->redirectToLearning($user, $formation, $progression, 'Vous devez être inscrit à cette formation.');
            }

            if (! $progression->isSectionUnlocked($user, $section)) {
                return redirect()->route('course.player', $formation->id)
                    ->with('error', 'Réussissez l’évaluation de la section précédente avant de continuer.');
            }

            if (! $progression->hasCompletedSectionChapters($user, $section)) {
                return redirect()->route('course.player', $formation->id)
                    ->with('error', 'Vous devez terminer tous les chapitres de la section avant de passer son examen.');
            }
        } elseif ($exam->examable_type === Formation::class) {
            $formation = $exam->examable;

            if (! $formation->is_certifying) {
                return $this->redirectToLearning($user, $formation, $progression, 'Cette formation ne prévoit pas d’examen final.');
            }

            if (! $this->isEnrolled($user, $formation)) {
                return $this->redirectToLearning($user, $formation, $progression, 'Vous devez être inscrit à cette formation.');
            }

            if (! $progression->areSectionsComplete($user, $formation)) {
                return redirect()->route('course.player', $formation->id)
                    ->with('error', 'Vous devez réussir les évaluations de toutes les sections avant l’examen final.');
            }
        }

        $attempt = DB::transaction(function () use ($exam, $user): ?ExamAttempt {
            Exam::query()->whereKey($exam->id)->lockForUpdate()->firstOrFail();

            $inProgressAttempt = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('user_id', $user->id)
                ->where('status', ExamAttemptEnum::IN_PROGRESS)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($inProgressAttempt?->hasExpired()) {
                $inProgressAttempt->expire();
                $inProgressAttempt = null;
            }

            if ($inProgressAttempt) {
                $inProgressAttempt->recordActivity();

                return $inProgressAttempt->refresh();
            }

            if (! $exam->canUserAttempt($user)) {
                return null;
            }

            return ExamAttempt::query()->create([
                'exam_id' => $exam->id,
                'user_id' => $user->id,
                'status' => ExamAttemptEnum::IN_PROGRESS,
                'started_at' => now(),
                'max_score' => $exam->getTotalPoints(),
            ]);
        }, 3);

        if (! $attempt) {
            return $this->redirectToLearning($user, $formation, $progression, 'Vous avez atteint le nombre maximum de tentatives pour cet examen. Contactez un administrateur pour demander une réouverture.');
        }

        if ($exam->randomize_questions && ! $attempt->question_order) {
            $order = $exam->questions()->pluck('id')->shuffle()->values()->toArray();
            $attempt->update(['question_order' => $order]);
            $attempt->refresh();
        }

        $timeRemaining = null;
        if ($attempt->expires_at) {
            $timeRemaining = (int) max(0, now()->diffInSeconds($attempt->expires_at, false));
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
            }
        }

        return Inertia::render('Student/Exams/Take', [
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
            'formation' => $formation ? [
                'id' => $formation->id,
                'title' => $formation->title,
                'slug' => $formation->slug,
            ] : null,
            'examContext' => $this->examContext($exam),
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

        if ($attempt->hasExpired()) {
            $attempt->expire();

            return response()->json([
                'saved' => false,
                'message' => 'Le temps imparti est écoulé. Cette tentative a expiré.',
            ], 410);
        }

        $question = $exam->questions()->findOrFail($validated['question_id']);
        $answer = $validated['answer'];

        $updateData = [];

        if (in_array($question->question_type, [QuestionTypeEnum::SINGLE_CHOICE, QuestionTypeEnum::TRUE_FALSE])) {
            $selectedOptionId = filter_var($answer, FILTER_VALIDATE_INT);
            abort_unless(
                $selectedOptionId !== false && $question->options()->whereKey($selectedOptionId)->exists(),
                422,
                'La réponse sélectionnée ne correspond pas à cette question.',
            );

            $updateData['selected_option_id'] = $selectedOptionId;
            $updateData['selected_options'] = null;
            $updateData['answer_text'] = null;
        } elseif ($question->question_type === QuestionTypeEnum::MULTIPLE_CHOICE) {
            $selectedOptionIds = collect(is_array($answer) ? $answer : [])
                ->map(fn (mixed $optionId): int => (int) $optionId)
                ->unique()
                ->values();
            $validOptionCount = $question->options()->whereKey($selectedOptionIds)->count();

            abort_unless(
                $selectedOptionIds->isNotEmpty() && $validOptionCount === $selectedOptionIds->count(),
                422,
                'Une ou plusieurs réponses ne correspondent pas à cette question.',
            );

            $updateData['selected_option_id'] = null;
            $updateData['selected_options'] = $selectedOptionIds->all();
            $updateData['answer_text'] = null;
        }

        UserAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            $updateData
        );

        $attempt->recordActivity();

        return response()->json(['saved' => true]);
    }

    public function submit(Exam $exam, CourseProgressionService $progression)
    {
        $user = auth()->user();
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', $user->id)
            ->where('status', ExamAttemptEnum::IN_PROGRESS)
            ->firstOrFail();

        if ($attempt->hasExpired()) {
            $attempt->expire();

            return redirect()->route('exam.results', $attempt)
                ->with('error', 'Le temps imparti est écoulé. La tentative a été clôturée.');
        }

        $requiredQuestionIds = $exam->questions()
            ->where('is_required', true)
            ->pluck('id');
        $answeredQuestionIds = $attempt->userAnswers()
            ->whereIn('question_id', $requiredQuestionIds)
            ->where(function ($query): void {
                $query->whereNotNull('selected_option_id')
                    ->orWhereNotNull('selected_options')
                    ->orWhereNotNull('answer_text');
            })
            ->pluck('question_id');

        if ($requiredQuestionIds->diff($answeredQuestionIds)->isNotEmpty()) {
            return back()->with('error', 'Répondez à toutes les questions obligatoires avant de soumettre l’examen.');
        }

        $attempt->complete();

        $formation = $this->resolveFormation($exam);
        if ($formation) {
            $progression->syncCompletion($user, $formation);
        }

        if ($exam->show_results_immediately) {
            return redirect()->to(route('exam.results', $attempt));
        }

        if ($exam->examable_type === Section::class && $formation) {
            $nextChapter = $this->nextSectionFirstChapter($exam, $formation, $progression);

            return redirect()->route('course.player', array_filter([
                'formation' => $formation->id,
                'chapterId' => $nextChapter?->id,
            ]))->with('success', 'Votre examen a été soumis avec succès!');
        }

        if ($exam->examable_type === Chapter::class) {
            $chapter = $exam->examable;
            $chapter->load('section.formation');

            if ($chapter->section && $chapter->section->formation) {
                return redirect()->route('course.player', [
                    'formation' => $chapter->section->formation->id,
                    'chapterId' => $chapter->id,
                ])->with('success', 'Votre examen a été soumis avec succès!');
            }
        }

        return redirect()->route('dashboard')->with('success', 'Votre examen a été soumis avec succès!');
    }

    public function results(ExamAttempt $attempt, CourseProgressionService $progression)
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

        $user = auth()->user();
        $passed = (float) $attempt->percentage >= $attempt->exam->getPassingScore();
        $canRetry = ! $passed && $attempt->exam->canUserAttempt($user);
        $formation = $this->resolveFormation($attempt->exam);
        $nextStep = $this->buildNextStep($attempt->exam, $formation, $passed, $progression);

        $certificate = $formation
            ? Certificate::query()
                ->where('user_id', $user->id)
                ->where('formation_id', $formation->id)
                ->where('status', CertificateStatusEnum::ACTIVE->value)
                ->first(['id', 'certificate_number', 'final_score'])
            : null;

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

        return Inertia::render('Student/Exams/Results', [
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
            'formation' => $formation ? [
                'id' => $formation->id,
                'title' => $formation->title,
                'slug' => $formation->slug,
            ] : null,
            'examContext' => $this->examContext($attempt->exam),
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
            'nextStep' => $nextStep,
            'certificate' => $certificate,
        ]);
    }

    private function redirectToLearning(
        User $user,
        ?Formation $formation,
        CourseProgressionService $progression,
        string $message,
    ): RedirectResponse {
        if (! $formation) {
            return redirect()->route('dashboard')->with('error', $message);
        }

        if (! $this->isEnrolled($user, $formation)) {
            return redirect()->route('student.learnings.detail', $formation->slug)->with('error', $message);
        }

        $chapterId = $progression->latestChapter($user, $formation)?->id;

        return redirect()->route('course.player', array_filter([
            'formation' => $formation->id,
            'chapterId' => $chapterId,
        ]))->with('error', $message);
    }

    private function isEnrolled(User $user, Formation $formation): bool
    {
        return Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('status', [
                EnrollmentStatusEnum::ACTIVE->value,
                EnrollmentStatusEnum::COMPLETED->value,
            ])
            ->whereIn('payment_status', [
                EnrollmentPaymentEnum::PAID->value,
                EnrollmentPaymentEnum::FREE->value,
            ])
            ->exists();
    }

    private function resolveFormation(Exam $exam): ?Formation
    {
        $examable = $exam->examable;

        if ($examable instanceof Formation) {
            return $examable;
        }

        if ($examable instanceof Section) {
            $examable->loadMissing('formation');

            return $examable->formation;
        }

        if ($examable instanceof Chapter) {
            $examable->loadMissing('section.formation');

            return $examable->section?->formation;
        }

        return null;
    }

    /**
     * @return array{type:string, label:string, parent_title:string|null}
     */
    private function examContext(Exam $exam): array
    {
        $examable = $exam->examable;

        if ($examable instanceof Formation) {
            return [
                'type' => 'formation',
                'label' => 'Examen final',
                'parent_title' => $examable->title,
            ];
        }

        if ($examable instanceof Section) {
            return [
                'type' => 'section',
                'label' => 'Évaluation de section',
                'parent_title' => $examable->title,
            ];
        }

        if ($examable instanceof Chapter) {
            return [
                'type' => 'chapter',
                'label' => 'Quiz du chapitre',
                'parent_title' => $examable->title,
            ];
        }

        return [
            'type' => 'exam',
            'label' => 'Évaluation',
            'parent_title' => null,
        ];
    }

    private function nextSectionFirstChapter(Exam $exam, Formation $formation, CourseProgressionService $progression): ?Chapter
    {
        $sections = $progression->orderedSections($formation);
        $index = $sections->search(fn (Section $section): bool => $section->id === $exam->examable_id);

        if ($index === false) {
            return null;
        }

        return $sections->get($index + 1)?->chapters->first();
    }

    /**
     * @return array{type:string, formation_id?:int, chapter_id?:int, exam_id?:int}|null
     */
    private function buildNextStep(Exam $exam, ?Formation $formation, bool $passed, CourseProgressionService $progression): ?array
    {
        if (! $formation) {
            return null;
        }

        if (! $passed) {
            return ['type' => 'retry'];
        }

        $user = auth()->user();

        if ($exam->examable_type === Formation::class) {
            return $passed
                ? ['type' => 'completed', 'formation_id' => $formation->id]
                : ['type' => 'retry'];
        }

        if ($exam->examable_type !== Section::class) {
            return null;
        }

        if ($progression->areSectionsComplete($user, $formation) && $formation->is_certifying) {
            $finalExam = $progression->formationExam($formation);

            return $finalExam
                ? ['type' => 'final_exam', 'formation_id' => $formation->id, 'exam_id' => $finalExam->id]
                : ['type' => 'final_exam_missing', 'formation_id' => $formation->id];
        }

        if ($progression->isFormationComplete($user, $formation)) {
            return ['type' => 'completed', 'formation_id' => $formation->id];
        }

        $nextChapter = $this->nextSectionFirstChapter($exam, $formation, $progression);

        if ($nextChapter) {
            return [
                'type' => 'next_section',
                'formation_id' => $formation->id,
                'chapter_id' => $nextChapter->id,
            ];
        }

        return ['type' => 'continue', 'formation_id' => $formation->id];
    }
}
