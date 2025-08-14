<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\ModuleResource;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(ModuleResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
            Actions\ViewAction::make()
                ->label('Voir')
                ->icon('heroicon-o-eye')
                ->url(ModuleResource::getUrl('view', ['record' => $this->record])),
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
            ->title('Utilisateur enregistré')
            ->body('L\'utilisateur a été mis à jour avec succès.');
    }
}
