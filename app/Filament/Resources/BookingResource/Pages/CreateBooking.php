<?php

declare(strict_types=1);

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

final class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string
    {
        return 'Créer une inscription';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title("Création de l'inscription")
            ->body('Vous pouvez maintenant continuer à ajouter des inscriptions.');
    }
}
