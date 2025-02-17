<?php

namespace App\Livewire\Pages;

use App\Models\Chapter;
use App\Models\MasterClass;
use App\Notifications\ExamSubmissionNotification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout("layouts.app")]
#[Title("Apprendre")]
class StudentCourseLearning extends Component implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    public ?array $data = [];

    public $file_path = null;

    public bool $examSubmitted = false;

    #[Locked]
    public MasterClass $masterClass;
    public ?Chapter $activeChapter = null;

    public function mount(MasterClass $masterClass): void
    {
        $this->form->fill();

        $this->masterClass = $masterClass->load(['resources', 'chapters', 'subscription']);
        $savedChapterId = session()->get("active_chapter_{$masterClass->id}");

        if ($savedChapterId) {
            $this->activeChapter = $this->masterClass->chapters->find($savedChapterId)->load('examination');
        }
    }

    public function render(): View
    {
        return view('livewire.pages.student-course-learning');
    }

    public function hasSubmittedExam(): bool
    {
        return $this->activeChapter->submission()
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make("Soumettre l'examen")
                    ->schema([
                        FileUpload::make('file_path')
                            ->label("Uploader le fichier")
                            ->required()
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('submission')
                            ->maxSize(10240)
                            ->downloadable()
                    ])
            ])
            ->statePath('data');
    }

    public function submitExam(): void
    {
        $examination = $this->activeChapter->examination;

        if ($examination->deadline && now()->isAfter($examination->deadline)) {
            $this->dispatch('notify', message: 'The submission deadline has passed.', type: 'error');
            return;
        }

        $this->validate([
            'data.file_path' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'], // 10MB max size
        ]);

        foreach ($this->data['file_path'] as $key => $path) {
            $this->file_path = $path->storePublicly(['disk' => 'submissions']);
        }

        $submission = $this->activeChapter->examination->submission()->create([
            'user_id' => Auth::user()->id,
            'chapter_id' => $this->activeChapter->id,
            'file_path' => $this->file_path,
            'submitted_at' => now()
        ]);

        defer(function () use ($submission) {
            Notification::sendNow(auth()->user(), new ExamSubmissionNotification($submission));
        });

        $this->examSubmitted = true;

        $this->completeChapter($this->activeChapter);
    }

    public function completeChapter(Chapter $chapter): void
    {
        // Update the current chapter's progress to completed
        $chapter->progress()->update([
            'status' => 'completed',
            'points_earned' => 100,
            'completed_at' => now()
        ]);

        // Find the next chapter
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

    public function setActiveChapter($chapterId): void
    {
        $previousChapter = $this->getPreviousChapter($chapterId);

        if ($previousChapter && !$previousChapter->isCompleted()) {
            $this->dispatch(
                'notify',
                message: "You must complete the previous chapter exam first.",
                type: "error"
            );
            return;
        }

        $chapter = $this->masterClass->chapters->find($chapterId)->load('examination');
        $this->activeChapter = $chapter;

        if (!$chapter->hasProgress()) {
            $chapter->progress()->create([
                'subscription_id' => $this->masterClass->subscription->id,
                'status' => 'in_progress',
                'points_earned' => 10
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
}
