<?php

declare(strict_types=1);

namespace App\Livewire\Pages\History;

use App\Models\ExamSubmission;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class StudentExamUpateHistory extends Component implements HasForms
{
    use InteractsWithForms;

    #[Locked]
    public ExamSubmission $submission;

    public array $data = [];

    public function render(): View
    {
        return view('livewire.pages.history.student-exam-upate-history');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file_path')
                    ->label('Fichier')
                    ->required()
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(1024 * 10)
                    ->preserveFilenames()
                    ->directory('exams'),
            ])
            ->statePath('data')
            ->model($this->submission);
    }

    public function update(): void
    {
        $this->form->validate();

        $student = Auth::user();

        if ($this->submission->user_id !== $student->id) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Vous ne pouvez pas modifier cette soumission d\'examen.'
            );

            return;
        }
        $data = $this->form->getState();

        $this->submission->update([
            'file_path' => $data['file_path'],
        ]);

        $this->dispatch(
            'notify',
            type: 'success',
            message: 'Soumission d\'examen mise à jour'
        );

        $this->redirect(route('student.history.lists'), navigate: true);
    }

    public function mount(ExamSubmission $submission): void
    {
        $this->submission = $submission;

        $data = $this->submission->attributesToArray();

        $this->form->fill($data);
    }
}
