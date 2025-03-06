<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\SubscriptionEnum;
use App\Models\Chapter;
use App\Models\MasterClass;
use App\Models\Subscription;
use App\Notifications\ExamSubmissionNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\LivewireFilepond\WithFilePond;

#[Layout('layouts.app')]
#[Title('Apprendre')]
final class StudentCourseLearning extends Component
{
    use WithFilePond;
    use WithFileUploads;

    #[Locked]
    public MasterClass $masterClass;

    public bool $isFinalChapter = false;

    #[Validate('required', 'file', 'mimes:pdf,doc,docx', 'max:5120')]
    public $file_path;

    public bool $examSubmitted = false;

    #[Locked]
    public ?Chapter $activeChapter;

    public function mount(MasterClass $masterClass): void
    {
        $this->masterClass = $masterClass->load(['resources', 'chapters', 'subscription']);
        $savedChapterId = session()->get("active_chapter_{$masterClass->id}");

        if ($savedChapterId) {
            $this->activeChapter = $this->masterClass->chapters->find($savedChapterId)->load(['examination', 'progress']);
        }
    }

    public function canSubmitExam(): bool
    {
        return Auth::user()->reference_code !== null;
    }

    public function getProgressPercentage(): int
    {
        if ($this->masterClass->chapters->isEmpty()) {
            return 0;
        }

        $completedChapters = $this->masterClass->chapters->filter(function ($chapter) {
            return $chapter->isCompleted();
        })->count();

        return (int)round(($completedChapters / $this->masterClass->chapters->count()) * 100);
    }

    public function setPreviousChapter(): void
    {
        $currentIndex = $this->masterClass->chapters()->getChapterIndex($this->activeChapter);

        if ($currentIndex > 0) {
            $previousChapter = $this->masterClass->chapters[$currentIndex - 1];
            $this->setActiveChapter($previousChapter->id);

            $this->dispatch(
                'notify',
                message: 'Passage au chapitre précédent.',
                type: 'success'
            );
        }
    }

    public function setActiveChapter($chapterId): void
    {
        $previousChapter = $this->getPreviousChapter($chapterId);

        $chapter = $this->masterClass->chapters->find($chapterId);

        if (!$this->canAccessChapter($chapter)) {
            $this->dispatch(
                'notify',
                message: 'Vous devez avoir un code de référence pour accéder à ce chapitre.',
                type: 'error'
            );

            return;
        }

        if ($previousChapter && !$previousChapter->isCompleted()) {
            $this->dispatch(
                'notify',
                message: 'You must complete the previous chapter exam first.',
                type: 'error'
            );

            return;
        }

        $chapter = $this->masterClass->chapters->find($chapterId)->load(['examination', 'progress']);
        $this->activeChapter = $chapter;

        if (!$this->masterClass->subscription()->whereBelongsTo(Auth::user())->exists()) {
            $this->masterClass->subscription()->create([
                'user_id' => Auth::user()->id,
                'status' => SubscriptionEnum::ACTIVE,
                'progress' => 0,
                'started_at' => now(),
            ]);

            $this->dispatch(
                'notify',
                message: 'Vous êtes maintenant inscrit à cette formation !',
                type: 'success'
            );
        }

        $subscriptionId = Subscription::query()
            ->whereBelongsTo(Auth::user())
            ->firstOrFail('id');

        if (!$chapter->hasProgress()) {
            $chapter->progress()->create([
                'subscription_id' => $subscriptionId->id,
                'status' => 'in_progress',
                'points_earned' => 0,
                'user_id' => Auth::id(),
            ]);
        }

        session()->put("active_chapter_{$this->masterClass->id}", $chapterId);
    }

    private function getPreviousChapter($currentChapterId): ?Chapter
    {
        $chapters = $this->masterClass->chapters;
        $currentIndex = $chapters->search(fn($chapter) => $chapter->id === $currentChapterId);

        return $currentIndex > 0 ? $chapters[$currentIndex - 1] : null;
    }

    public function canAccessChapter(Chapter $chapter): bool
    {
        if ($chapter === $this->masterClass->chapters->first()) {
            return true;
        }

        return auth()->user()->reference_code !== null;
    }

    public function setNextChapter(): void
    {
        if (!$this->activeChapter->isCompleted()) {
            $this->dispatch(
                'notify',
                message: 'You must complete the current chapter before moving to the next one.',
                type: 'error'
            );

            return;
        }

        $currentIndex = $this->masterClass->chapters->search($this->activeChapter);
        if ($currentIndex < $this->masterClass->chapters->count() - 1) {
            $nextChapter = $this->masterClass->chapters[$currentIndex + 1];
            if (auth()->user()->reference_code === null) {
                $this->dispatch(
                    'notify',
                    message: 'Vous devez avoir un code de référence pour accéder au chapitre suivant.',
                    type: 'error'
                );

                return;
            }

            $this->setActiveChapter($nextChapter->id);

            $this->dispatch(
                'notify',
                message: 'Moving to next chapter.',
                type: 'success'
            );
        }
    }

    public function render(): View
    {
        return view('livewire.pages.student-course-learning');
    }

    public function submitExam(): void
    {
        $this->validate();

        $examination = $this->activeChapter->examination;

        if ($examination->deadline && now()->isAfter($examination->deadline)) {
            $this->dispatch('notify', message: 'Le delai est deja depasser pour soumettre votre examen', type: 'error');

            return;
        }

        $path = $this->file_path->storePublicly('/', ['disk' => 'public']);

        $submission = $this->activeChapter->examination->submission()->create([
            'user_id' => Auth::user()->id,
            'chapter_id' => $this->activeChapter->id,
            'file_path' => $path,
            'submitted_at' => now(),
        ]);

        defer(function () use ($submission) {
            Notification::sendNow(auth()->user(), new ExamSubmissionNotification($submission));
        });

        $this->examSubmitted = true;
        $this->file_path = null;
    }

    public function completeChapter(Chapter $chapter): void
    {
        // Vérifier si le chapitre a un examen et s'il n'a pas été soumis
        if ($chapter->examination && !$chapter->submission()->where('user_id', '=', Auth::user()->id)->exists()) {
            $this->dispatch(
                'notify',
                message: 'Vous devez soumettre l\'examen avant de terminer ce chapitre.',
                type: 'error'
            );

            return;
        }

        // Mise à jour du progrès avec une seule requête
        $chapter->progress()->updateOrCreate(
            [
                'user_id' => Auth::user()->id,
            ],
            [
                'status' => 'completed',
                'points_earned' => 1,
                'completed_at' => now(),
            ]
        );

        // Optimisation de la recherche du prochain chapitre
        $chapters = $this->masterClass->chapters;
        $currentIndex = $chapters->search(fn($ch) => $ch->id === $chapter->id);
        $nextChapter = $currentIndex < $chapters->count() - 1 ? $chapters[$currentIndex + 1] : null;

        if ($nextChapter) {
            $this->setActiveChapter($nextChapter->id);
            $this->dispatch(
                'notify',
                message: 'Chapter completed! Moving to next chapter.',
                type: 'success'
            );
        } else {
            $this->dispatch(
                'notify',
                message: 'Congratulations! You have completed all chapters.',
                type: 'success'
            );
        }
    }

    public function hasSubmittedExam(): bool
    {
        return $this->activeChapter->submission()
            ->where('user_id', '=', Auth::user()->id)
            ->exists();
    }
}
