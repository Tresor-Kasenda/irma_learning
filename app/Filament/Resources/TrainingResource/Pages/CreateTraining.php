<?php

namespace App\Filament\Resources\TrainingResource\Pages;

use App\Filament\Resources\TrainingResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateTraining extends CreateRecord
{
    protected static string $resource = TrainingResource::class;

    public function getHeading(): Htmlable|string
    {
        return "Créer une formation";
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Formation créée')
            ->body('La formation a été créée avec succès.');
    }
}
