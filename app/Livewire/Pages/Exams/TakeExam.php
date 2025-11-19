<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Exams;

use App\Enums\ExamAttemptEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\UserAnswer;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Passer l\'examen')]
final class TakeExam extends Component
{
    public Exam $exam;

    public ExamAttempt $attempt;

    public array $answers = [];

    public ?int $timeRemaining = null;

    public int $currentQuestionIndex = 0;

    public function mount(Exam $exam): void
    {
        // Vérifier que l'examen est actif
        if (! $exam->is_active) {
            abort(403, 'Cet examen n\'est pas disponible.');
        }

        // Vérifier les dates de disponibilité
        if ($exam->available_from && now()->isBefore($exam->available_from)) {
            abort(403, 'Cet examen n\'est pas encore disponible.');
        }

        if ($exam->available_until && now()->isAfter($exam->available_until)) {
            abort(403, 'Cet examen n\'est plus disponible.');
        }

        // Vérifier que l'utilisateur peut passer l'examen
        if (! $exam->canUserAttempt(auth()->user())) {
            abort(403, 'Vous avez atteint le nombre maximum de tentatives pour cet examen.');
        }

        // Créer ou récupérer une tentative en cours
        $this->attempt = ExamAttempt::firstOrCreate(
            [
                'exam_id' => $exam->id,
                'user_id' => auth()->id(),
                'status' => ExamAttemptEnum::IN_PROGRESS,
            ],
            [
                'started_at' => now(),
                'max_score' => $exam->getTotalPoints(),
            ]
        );

        // Initialiser le timer si défini
        if ($exam->duration_minutes) {
            $elapsed = now()->diffInSeconds($this->attempt->started_at);
            $totalSeconds = $exam->duration_minutes * 60;
            $this->timeRemaining = max(0, $totalSeconds - $elapsed);
        }

        // Charger les réponses existantes
        $this->loadExistingAnswers();
    }

    #[Computed]
    public function questions()
    {
        $questions = $this->exam->questions()->with('options')->get();

        if ($this->exam->randomize_questions && ! $this->attempt->question_order) {
            $order = $questions->pluck('id')->shuffle()->values()->toArray();
            $this->attempt->update(['question_order' => $order]);
        }

        if ($this->attempt->question_order) {
            $questions = $questions->sortBy(function ($question) {
                return array_search($question->id, $this->attempt->question_order);
            })->values();
        }

        return $questions;
    }

    #[Computed]
    public function currentQuestion()
    {
        return $this->questions()[$this->currentQuestionIndex] ?? null;
    }

    #[Computed]
    public function progress(): float
    {
        $answered = count(array_filter($this->answers));
        $total = $this->questions()->count();

        return $total > 0 ? ($answered / $total) * 100 : 0;
    }

    public function loadExistingAnswers(): void
    {
        $userAnswers = UserAnswer::query()
            ->where('exam_attempt_id', $this->attempt->id)
            ->get();

        foreach ($userAnswers as $userAnswer) {
            $questionId = $userAnswer->question_id;

            if ($userAnswer->question->question_type === QuestionTypeEnum::MULTIPLE_CHOICE) {
                $this->answers[$questionId] = json_decode($userAnswer->selected_options ?? '[]', true);
            } elseif ($userAnswer->question->question_type === QuestionTypeEnum::SINGLE_CHOICE) {
                $this->answers[$questionId] = $userAnswer->selected_option_id;
            } elseif (in_array($userAnswer->question->question_type, [QuestionTypeEnum::TEXT, QuestionTypeEnum::ESSAY])) {
                $this->answers[$questionId] = $userAnswer->answer_text;
            }
        }
    }

    public function saveAnswer(int $questionId): void
    {
        $question = $this->questions()->firstWhere('id', $questionId);

        if (! $question) {
            return;
        }

        $answer = $this->answers[$questionId] ?? null;

        UserAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $this->attempt->id,
                'question_id' => $questionId,
            ],
            [
                'selected_option_id' => $question->question_type === QuestionTypeEnum::SINGLE_CHOICE ? $answer : null,
                'selected_options' => $question->question_type === QuestionTypeEnum::MULTIPLE_CHOICE ? json_encode($answer ?? []) : null,
                'answer_text' => in_array($question->question_type, [QuestionTypeEnum::TEXT, QuestionTypeEnum::ESSAY]) ? $answer : null,
            ]
        );
    }

    public function nextQuestion(): void
    {
        if ($this->currentQuestion) {
            $this->saveAnswer($this->currentQuestion->id);
        }

        if ($this->currentQuestionIndex < $this->questions()->count() - 1) {
            $this->currentQuestionIndex++;
        }
    }

    public function previousQuestion(): void
    {
        if ($this->currentQuestion) {
            $this->saveAnswer($this->currentQuestion->id);
        }

        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
        }
    }

    public function goToQuestion(int $index): void
    {
        if ($this->currentQuestion) {
            $this->saveAnswer($this->currentQuestion->id);
        }

        if ($index >= 0 && $index < $this->questions()->count()) {
            $this->currentQuestionIndex = $index;
        }
    }

    public function submitExam(): void
    {
        // Sauvegarder la dernière réponse
        if ($this->currentQuestion) {
            $this->saveAnswer($this->currentQuestion->id);
        }

        DB::transaction(function () {
            // Calculer les scores
            $userAnswers = UserAnswer::query()
                ->where('exam_attempt_id', $this->attempt->id)
                ->get();

            foreach ($userAnswers as $userAnswer) {
                $userAnswer->checkCorrectness();
            }

            $totalScore = $userAnswers->sum('points_earned');
            $maxScore = $this->exam->getTotalPoints();
            $percentage = $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0;

            // Mettre à jour la tentative
            $this->attempt->update([
                'status' => ExamAttemptEnum::COMPLETED,
                'completed_at' => now(),
                'score' => $totalScore,
                'percentage' => $percentage,
            ]);

            // Si l'examen est lié à un chapitre et réussi, marquer le chapitre comme terminé
            if ($this->exam->examable_type === 'App\Models\Chapter' && $percentage >= ($this->exam->passing_score ?? 70)) {
                $chapter = $this->exam->examable;
                
                // Mettre à jour ou créer la progression du chapitre
                \App\Models\UserProgress::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'trackable_type' => 'App\Models\Chapter',
                        'trackable_id' => $chapter->id,
                    ],
                    [
                        'progress_percentage' => 100,
                        'status' => \App\Enums\UserProgressEnum::COMPLETED,
                        'completed_at' => now(),
                        'time_spent' => ($chapter->duration_minutes ?? 0) * 60,
                    ]
                );
            }
        });

        session()->flash('success', 'Votre examen a été soumis avec succès!');

        if ($this->exam->show_results_immediately) {
            $this->redirect(route('exam.results', $this->attempt), navigate: true);

            return;
        }

        // Redirection contextuelle
        if ($this->exam->examable_type === 'App\Models\Chapter') {
            $chapter = $this->exam->examable;
            $chapter->load('section.formation');
            if ($chapter->section && $chapter->section->formation) {
                $this->redirect(route('course.player', ['formation' => $chapter->section->formation->slug, 'chapterId' => $chapter->id]), navigate: true);
                return;
            }
        }

        $this->redirect(route('dashboard'), navigate: true);

    }

    public function render()
    {
        return view('livewire.pages.exams.take-exam');
    }
}
