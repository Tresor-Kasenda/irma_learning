<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Models\Chapter;
use App\Models\Enrollment;
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
            ->where('status', EnrollmentStatusEnum::Active)
            ->first();

        if (! $this->enrollment) {
            $this->dispatch('notify', message: 'Vous devez être inscrit à cette formation pour y accéder.', type: 'error');
            $this->redirect(route('formation.show', $formation->slug), navigate: true);

            return;
        }

        // Récupérer tous les chapitres dans l'ordre
        $this->allChapters = $this->formation->modules
            ->flatMap(fn ($module) => $module->sections)
            ->flatMap(fn ($section) => $section->chapters)
            ->values()
            ->toArray();

        // Définir le chapitre actuel
        if ($chapterId) {
            $this->currentChapter = Chapter::find($chapterId);
            $this->currentChapterIndex = collect($this->allChapters)->search(fn ($ch) => $ch['id'] === $chapterId);
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
                $this->currentChapterIndex = collect($this->allChapters)->search(fn ($ch) => $ch['id'] === $lastProgress->trackable_id);
            } else {
                $this->currentChapter = Chapter::find($this->allChapters[0]['id']);
                $this->currentChapterIndex = 0;
            }
        }

        // Marquer comme en cours
        $this->markChapterAsInProgress();
    }

    public function selectChapter(int $chapterId): void
    {
        $this->currentChapter = Chapter::find($chapterId);
        $this->currentChapterIndex = collect($this->allChapters)->search(fn ($ch) => $ch['id'] === $chapterId);
        $this->markChapterAsInProgress();
    }

    public function markChapterAsCompleted(): void
    {
        if (! $this->currentChapter) {
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

    public function previousChapter(): void
    {
        if ($this->currentChapterIndex > 0) {
            $this->currentChapterIndex--;
            $prevChapterId = $this->allChapters[$this->currentChapterIndex]['id'];
            $this->selectChapter($prevChapterId);
        }
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

    public function render(): View
    {
        return view('livewire.pages.courses.course-player', [
            'completedChapters' => $this->getCompletedChaptersProperty(),
            'htmlContent' => $this->currentChapter?->getHtmlContent() ?? '',
        ]);
    }

    private function markChapterAsInProgress(): void
    {
        if (! $this->currentChapter) {
            return;
        }

        $progress = UserProgress::where([
            'user_id' => auth()->id(),
            'trackable_type' => Chapter::class,
            'trackable_id' => $this->currentChapter->id,
        ])->first();

        if (! $progress) {
            UserProgress::create([
                'user_id' => auth()->id(),
                'trackable_type' => Chapter::class,
                'trackable_id' => $this->currentChapter->id,
                'status' => UserProgressEnum::IN_PROGRESS,
                'started_at' => now(),
            ]);
        }
    }

    private function updateEnrollmentProgress(): void
    {
        if (! $this->enrollment) {
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
            'status' => $progressPercentage >= 100 ? EnrollmentStatusEnum::Completed : EnrollmentStatusEnum::Active,
            'completion_date' => $progressPercentage >= 100 ? now() : null,
        ]);
    }
}
