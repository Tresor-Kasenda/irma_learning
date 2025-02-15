<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventTypeResource\Pages;

use App\Filament\Resources\EventTypeResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

final class CreateEventType extends CreateRecord
{
    protected static string $resource = EventTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title("Type d'événement créé")
            ->body('Le type d\'événement a été créé avec succès.');
    }
}
