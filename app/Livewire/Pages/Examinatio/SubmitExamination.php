<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Examinatio;

use App\Models\MasterClass;
use App\Notifications\FinalExamSubmissionNotification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class SubmitExamination extends Component implements HasForms
{
    use InteractsWithForms;

    #[Locked]
    public MasterClass $masterClass;

    public ?array $data = [];

    #[Validate('required|file')]
    public $file_path;

    public function mount(MasterClass $masterClass): void
    {
        if (! $this->canAccessFinalExam()) {
            $this->redirect(route('student.course.learning', $masterClass));
        }

        if ($this->hasSubmittedFinalExam()) {
            $this->dispatch('notify',
                message: 'Vous avez déjà soumis votre examen final.',
                type: 'warning'
            );
        }

        $this->masterClass = $masterClass->load('finalExam');
        $this->form->fill();
    }

    public function hasSubmittedFinalExam(): bool
    {
        return $this->masterClass->examFinal()
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file_path')
                    ->label('Votre réponse')
                    ->required()
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(10240)
                    ->disabled($this->hasSubmittedFinalExam())
                    ->directory('final-exam-submissions'),
            ])
            ->disabled($this->hasSubmittedFinalExam());
    }

    public function submitFinalExam(): void
    {
        $this->validate();

        $data = $this->form->getState();

        $submission = $this->masterClass->examFinal()->create([
            'user_id' => Auth::id(),
            'file_path' => $data['file_path'],
            'submitted_at' => now(),
        ]);

        Notification::send(
            $submission->user,
            new FinalExamSubmissionNotification($submission)
        );

        $this->dispatch('notify',
            message: 'Votre examen final a été soumis avec succès!',
            type: 'success'
        );

        $this->redirect(route('student.course.learning', [
            'masterClass' => $this->masterClass,
        ]));
    }

    public function render(): View
    {
        return view('livewire.pages.examinatio.submit-examination');
    }

    private function canAccessFinalExam(): bool
    {
        return $this->masterClass->chapters->every(fn ($chapter) => $chapter->isCompleted());
    }
}
