<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Exams;

use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Résultats de l\'examen')]
final class ExamResults extends Component
{
    public ExamAttempt $attempt;

    public function mount(ExamAttempt $attempt): void
    {
        // Vérifier que l'utilisateur peut voir ces résultats
        Gate::authorize('view', $attempt);
    }

    #[Computed]
    public function userAnswers()
    {
        return $this->attempt->userAnswers()
            ->with(['question.options', 'selectedOption'])
            ->get()
            ->sortBy(function ($answer) {
                if ($this->attempt->question_order) {
                    return array_search($answer->question_id, $this->attempt->question_order);
                }

                return $answer->question->order_position;
            });
    }

    #[Computed]
    public function passed(): bool
    {
        return $this->attempt->percentage >= $this->attempt->exam->getPassingScore();
    }

    public function render()
    {
        return view('livewire.pages.exams.exam-results');
    }
}
