<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\UserProgress;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formation')]
final class CoursePlayer extends Component
{
    #[Locked]
    public Formation $formation;

    public ?Chapter $currentChapter = null;

    public ?Enrollment $enrollment = null;

    public $allChapters = [];

    public $currentChapterIndex = 0;

    public function mount(Formation $formation, ?int $chapterId = null): void
    {
        $this->formation = $formation->load([
            'modules.sections.chapters' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('order_position');
            },
        ]);

        // Vérifier l'inscription
        $this->enrollment = auth()->user()
            ->enrollments()
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->where('status', EnrollmentStatusEnum::ACTIVE)
            ->first();

        if (!$this->enrollment) {
            $this->dispatch('notify', message: 'Vous devez être inscrit à cette formation pour y accéder.', type: 'error');
            $this->redirect(route('formation.show', $formation->slug), navigate: true);

            return;
        }

        // Récupérer tous les chapitres dans l'ordre
        $this->allChapters = $this->formation->modules
            ->flatMap(fn($module) => $module->sections)
            ->flatMap(fn($section) => $section->chapters)
            ->values()
            ->toArray();

        // Définir le chapitre actuel
        if ($chapterId) {
            $this->currentChapter = Chapter::find($chapterId);
            $this->currentChapterIndex = collect($this->allChapters)->search(fn($ch) => $ch['id'] === $chapterId);
        } else {
            // Charger le dernier chapitre en cours ou le premier
            $lastProgress = UserProgress::where('user_id', auth()->id())
                ->where('trackable_type', Chapter::class)
                ->whereIn('trackable_id', collect($this->allChapters)->pluck('id'))
                ->where('status', UserProgressEnum::IN_PROGRESS)
                ->latest('updated_at')
                ->first();

            if ($lastProgress) {
                $this->currentChapter = Chapter::find($lastProgress->trackable_id);
                $this->currentChapterIndex = collect($this->allChapters)->search(fn($ch) => $ch['id'] === $lastProgress->trackable_id);
            } else {
                $this->currentChapter = Chapter::find($this->allChapters[0]['id']);
                $this->currentChapterIndex = 0;
            }
        }

        // Marquer comme en cours
        $this->markChapterAsInProgress();
    }

    private function markChapterAsInProgress(): void
    {
        if (!$this->currentChapter) {
            return;
        }

        $progress = UserProgress::where([
            'user_id' => auth()->id(),
            'trackable_type' => Chapter::class,
            'trackable_id' => $this->currentChapter->id,
        ])->first();

        if (!$progress) {
            UserProgress::create([
                'user_id' => auth()->id(),
                'trackable_type' => Chapter::class,
                'trackable_id' => $this->currentChapter->id,
                'status' => UserProgressEnum::IN_PROGRESS,
                'started_at' => now(),
            ]);
        }
    }

    public function getChapterExamProperty(): ?Exam
    {
        if (!$this->currentChapter) {
            return null;
        }

        return $this->currentChapter->exams()->active()->first();
    }

    public function takeExam(): void
    {
        if (!$this->chapterExam) {
            return;
        }

        $this->redirect(route('exam.take', $this->chapterExam), navigate: true);
    }

    public function markChapterAsCompleted(): void
    {
        if (!$this->currentChapter) {
            return;
        }

        // Vérifier si un examen est requis et non passé
        if ($this->chapterExam && !$this->hasPassedExam()) {
            $this->dispatch('notify', message: 'Vous devez réussir l\'examen pour valider ce chapitre.', type: 'warning');
            return;
        }

        $progress = UserProgress::where([
            'user_id' => auth()->id(),
            'trackable_type' => Chapter::class,
            'trackable_id' => $this->currentChapter->id,
        ])->first();

        if ($progress) {
            $progress->update([
                'progress_percentage' => 100,
                'status' => UserProgressEnum::COMPLETED,
                'completed_at' => now(),
                'time_spent' => ($this->currentChapter->duration_minutes ?? 0) * 60,
            ]);
        } else {
            UserProgress::create([
                'user_id' => auth()->id(),
                'trackable_type' => Chapter::class,
                'trackable_id' => $this->currentChapter->id,
                'progress_percentage' => 100,
                'status' => UserProgressEnum::COMPLETED,
                'completed_at' => now(),
                'time_spent' => ($this->currentChapter->duration_minutes ?? 0) * 60,
            ]);
        }

        // Mettre à jour la progression de l'enrollment
        $this->updateEnrollmentProgress();

        $this->dispatch('notify', message: 'Chapitre complété avec succès !', type: 'success');

        // Passer au chapitre suivant automatiquement
        $this->nextChapter();
    }

    public function hasPassedExam(): bool
    {
        $exam = $this->chapterExam;

        if (!$exam) {
            return true;
        }

        return $exam->attempts()
            ->where('user_id', auth()->id())
            ->where('score', '>=', $exam->passing_score ?? 70) // Fallback to 70 if null, though model has default
            ->exists();
    }

    private function updateEnrollmentProgress(): void
    {
        if (!$this->enrollment) {
            return;
        }

        $totalChapters = count($this->allChapters);
        $completedChapters = UserProgress::where('user_id', auth()->id())
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', collect($this->allChapters)->pluck('id'))
            ->where('status', UserProgressEnum::COMPLETED)
            ->count();

        $progressPercentage = $totalChapters > 0 ? ($completedChapters / $totalChapters) * 100 : 0;

        $this->enrollment->update([
            'progress_percentage' => round($progressPercentage, 2),
            'status' => $progressPercentage >= 100 ? EnrollmentStatusEnum::COMPLETED : EnrollmentStatusEnum::ACTIVE,
            'completion_date' => $progressPercentage >= 100 ? now() : null,
        ]);
    }

    public function nextChapter(): void
    {
        if ($this->currentChapterIndex < count($this->allChapters) - 1) {
            $this->currentChapterIndex++;
            $nextChapterId = $this->allChapters[$this->currentChapterIndex]['id'];
            $this->selectChapter($nextChapterId);
        } else {
            $this->dispatch('notify', message: 'Vous avez terminé tous les chapitres de cette formation !', type: 'success');
        }
    }

    public function selectChapter(int $chapterId): void
    {
        $this->currentChapter = Chapter::find($chapterId);
        $this->currentChapterIndex = collect($this->allChapters)->search(fn($ch) => $ch['id'] === $chapterId);
        $this->markChapterAsInProgress();
    }

    public function previousChapter(): void
    {
        if ($this->currentChapterIndex > 0) {
            $this->currentChapterIndex--;
            $prevChapterId = $this->allChapters[$this->currentChapterIndex]['id'];
            $this->selectChapter($prevChapterId);
        }
    }

    public function render(): View
    {
        return view('livewire.pages.courses.course-player', [
            'completedChapters' => $this->getCompletedChaptersProperty(),
            'htmlContent' => $this->currentChapter?->getHtmlContent() ?? '',
            'chapterExam' => $this->chapterExam,
            'hasPassedExam' => $this->hasPassedExam(),
        ]);
    }

    public function getCompletedChaptersProperty(): array
    {
        return UserProgress::where('user_id', auth()->id())
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', collect($this->allChapters)->pluck('id'))
            ->where('status', UserProgressEnum::COMPLETED)
            ->pluck('trackable_id')
            ->toArray();
    }
}
