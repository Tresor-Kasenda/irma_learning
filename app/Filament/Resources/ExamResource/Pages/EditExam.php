<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditExam extends EditRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(ExamResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // unset data
        unset(
            $data['formation_id'],
            $data['module_id'],
            $data['section_id']
        );
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Exam saved')
            ->body('The exam has been successfully updated.');
    }
}
