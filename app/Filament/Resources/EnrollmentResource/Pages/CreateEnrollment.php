<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Succès')
            ->body('L\'inscription a été effectuée avec succès.');
    }
}
