<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateExam extends CreateRecord
{
    protected static string $resource = ExamResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Examen créé avec succès')
            ->body('L\'examen a été créé avec succès.');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset(
            $data['formation_id'],
            $data['module_id'],
            $data['section_id']
        );
        return $data;
    }
}
