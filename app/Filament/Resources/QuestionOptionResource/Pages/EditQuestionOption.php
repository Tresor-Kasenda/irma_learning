<?php

namespace App\Filament\Resources\QuestionOptionResource\Pages;

use App\Filament\Resources\QuestionOptionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuestionOption extends EditRecord
{
    protected static string $resource = QuestionOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->icon('heroicon-o-arrow-left')
                ->url(QuestionOptionResource::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Formation mise à jour')
            ->body('L\'option de question a été mise à jour avec succès.');
    }
}
