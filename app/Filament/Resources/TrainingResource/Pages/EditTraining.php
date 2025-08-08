<?php

namespace App\Filament\Resources\TrainingResource\Pages;

use App\Filament\Resources\TrainingResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTraining extends EditRecord
{
    protected static string $resource = TrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Retour')
                ->url(TrainingResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->label('Retour')
        ];
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Formation mise à jour')
            ->body('La formation a été mise à jour avec succès.');
    }
}
