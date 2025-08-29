<?php

namespace App\Filament\Resources\QuestionOptionResource\Pages;

use App\Filament\Resources\QuestionOptionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionOption extends CreateRecord
{
    protected static string $resource = QuestionOptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Option de question créée avec succès')
            ->body('L\'option de question a été créée avec succès.');
    }
}
