<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormationResource\Pages;

use App\Filament\Resources\FormationResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

final class CreateFormation extends CreateRecord
{
    protected static string $resource = FormationResource::class;

    protected static ?string $title = 'Ajouter une formation';

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Formation Created')
            ->body('The formation has been successfully created.');
    }
}
