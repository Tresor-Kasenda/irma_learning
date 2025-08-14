<?php

namespace App\Filament\Resources\FormationResource\Pages;

use App\Filament\Resources\FormationResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditFormation extends EditRecord
{
    protected static string $resource = FormationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(FormationResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
            Actions\ViewAction::make()
                ->label('Voir')
                ->icon('heroicon-o-eye')
                ->url(FormationResource::getUrl('view', ['record' => $this->record])),
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
            ->title('Formation saved')
            ->body('The formation has been successfully updated.');
    }
}
