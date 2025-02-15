<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

final class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string
    {
        return "Modifier l'inscription";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Supprimer')
                ->icon('heroicon-o-trash'),
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
            ->title("Modification de l'inscription")
            ->body('Vous pouvez maintenant continuer à ajouter des inscriptions.');
    }
}
