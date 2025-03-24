<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\SubscriptionEnum;
use App\Models\Chapter;
use App\Models\MasterClass;
use App\Models\Subscription;
use App\Notifications\ExamSubmissionNotification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Apprendre')]
final class StudentCourseLearning extends Component implements HasForms
{
    use InteractsWithForms;

    #[Locked]
    public MasterClass $masterClass;

    public bool $isFinalChapter = false;

    public ?array $data = [];

    public bool $examSubmitted = false;

    #[Locked]
    public ?Chapter $activeChapter;

    public function mount(MasterClass $masterClass, ?string $chapter = null): void
    {
        $this->masterClass = $masterClass->load(['resources', 'chapters', 'subscription']);

        if ($chapter) {
            $matchingChapter = $this->masterClass->chapters()
                ->where('title', 'like', Str::replace('-', ' ', $chapter))
                ->first();

            if ($matchingChapter) {
                $this->setActiveChapter($matchingChapter->id);
                return;
            }
        }

        $savedChapterId = session()->get("active_chapter_{$masterClass->id}");
        if ($savedChapterId) {
            $this->activeChapter = $this->masterClass->chapters->find($savedChapterId)->load(['examination', 'progress']);
        }

        $this->form->fill();
    }

    public function setActiveChapter($chapterId): void
    {
        $previousChapter = $this->getPreviousChapter($chapterId);
        $chapter = $this->masterClass->chapters->find($chapterId);

        if (!$this->canAccessChapter($chapter)) {
            $this->dispatch('notify', message: 'Vous devez avoir un code de référence pour accéder à ce chapitre.', type: 'error');
            return;
        }

        if ($previousChapter && !$previousChapter->isCompleted()) {
            $this->dispatch('notify', message: 'Vous devez d\'abord passer l\'examen du chapitre précédent.', type: 'error');
            return;
        }

        $this->activeChapter = $chapter->load(['examination', 'progress']);
        $this->dispatch('urlChanged', url: route('student.course.learning', [
            'masterClass' => $this->masterClass->id,
            'chapter' => Str::slug($chapter->title)
        ]));

        if (!$this->masterClass->subscription()->whereBelongsTo(Auth::user())->exists()) {
            $this->masterClass->subscription()->create([
                'user_id' => Auth::user()->id,
                'status' => SubscriptionEnum::ACTIVE,
                'progress' => 0,
                'started_at' => now(),
            ]);
            $this->dispatch('notify', message: 'Vous êtes maintenant inscrit à cette formation !', type: 'success');
        }

        $subscriptionId = Subscription::query()->whereBelongsTo(Auth::user())->firstOrFail('id');

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
        $currentIndex = $this->masterClass->chapters->search(function ($chapter) {
            return $chapter->id === $this->activeChapter->id;
        });

        if ($currentIndex > 0) {
            $previousChapter = $this->masterClass->chapters[$currentIndex - 1];
            $this->setActiveChapter($previousChapter->id);
        }
    }

    public function setNextChapter(): void
    {
        if (!$this->activeChapter->isCompleted()) {
            $this->dispatch(
                'notify',
                message: 'Vous devez terminer le chapitre en cours avant de passer au suivant.',
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
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file_path')
                    ->label("Soumettre l'examen")
                    ->placeholder("Choisissez un fichier")
                    ->directory('examens')
                    ->downloadable()
                    ->previewable()
                    ->required()
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(10240) // Taille maximale de 10MB
                    ->deletable()
                    ->uploadingMessage('Uploading certification...')
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function render(): View
    {
        return view('livewire.pages.student-course-learning');
    }

    public function submitExam(): void
    {
        $this->validate([
            'data.file_path' => 'required|array|min:1',
        ], [
            'data.file_path.required' => 'Veuillez sélectionner un fichier à soumettre.',
            'data.file_path.array' => 'Format de fichier invalide.',
            'data.file_path.min' => 'Veuillez sélectionner au moins un fichier.',
        ]);

        $examination = $this->activeChapter->examination;

        if ($examination->deadline && now()->isAfter($examination->deadline)) {
            $this->dispatch('notify', message: 'Le delai est deja depasser pour soumettre votre examen', type: 'error');

            return;
        }

        $data = $this->form->getState();


        $submission = $this->activeChapter->examination->submission()->create([
            'user_id' => Auth::user()->id,
            'chapter_id' => $this->activeChapter->id,
            'file_path' => $data['file_path'],
            'submitted_at' => now(),
        ]);

        defer(function () use ($submission) {
            Notification::sendNow(auth()->user(), new ExamSubmissionNotification($submission));
        });

        $this->examSubmitted = true;
        $this->form->fill();
        $this->data = [];
    }

    public function completeChapter(Chapter $chapter): void
    {
        if ($chapter->examination && !$chapter->submission()->where('user_id', '=', Auth::user()->id)->exists()) {
            $this->dispatch(
                'notify',
                message: 'Vous devez soumettre l\'examen avant de terminer ce chapitre.',
                type: 'error'
            );
            return;
        }

        // Mise à jour du progrès du chapitre
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

        // Calculer et mettre à jour la progression globale
        $totalChapters = $this->masterClass->chapters->count();
        $completedChapters = $this->masterClass->chapters()
            ->whereHas('progress', function ($query) {
                $query->where('user_id', Auth::user()->id)
                    ->where('status', 'completed');
            })
            ->count();

        $progressPercentage = ($completedChapters / $totalChapters) * 100;

        // Mettre à jour la progression dans la subscription
        $this->masterClass->subscription()
            ->whereBelongsTo(Auth::user())
            ->update(['progress' => $progressPercentage, 'completed_at' => now()]);

        // Vérifier si c'est le dernier chapitre
        $isLastChapter = $chapter->id === $this->masterClass->chapters->last()->id;

        if ($isLastChapter && $this->hasCompletedAllChapters()) {
            $this->dispatch(
                'notify',
                message: 'Félicitations ! Vous avez terminé tous les chapitres. Vous pouvez maintenant passer l\'examen final.',
                type: 'success'
            );

            // Redirection vers l'examen final
            $this->redirect(route('student.course.final-exam', ['masterClass' => $this->masterClass]), navigate: true);
        }

        // Gestion du chapitre suivant si ce n'est pas le dernier
        $chapters = $this->masterClass->chapters;
        $currentIndex = $chapters->search(fn($ch) => $ch->id === $chapter->id);
        $nextChapter = $currentIndex < $chapters->count() - 1 ? $chapters[$currentIndex + 1] : null;

        if ($nextChapter) {
            $this->setActiveChapter($nextChapter->id);
            $this->dispatch(
                'notify',
                message: 'Chapitre terminé ! Passage au chapitre suivant.',
                type: 'success'
            );
        }
    }

    private function hasCompletedAllChapters(): bool
    {
        return $this->masterClass->chapters()
            ->whereDoesntHave('progress', function ($query) {
                $query->where('user_id', Auth::user()->id)
                    ->where('status', 'completed');
            })
            ->doesntExist();
    }

    public function hasSubmittedExam(): bool
    {
        return $this->activeChapter->submission()
            ->where('user_id', '=', Auth::user()->id)
            ->exists();
    }
}
