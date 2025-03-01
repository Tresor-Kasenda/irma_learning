<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamResultResource\Pages;

use App\Filament\Resources\ExamResultResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

final class EditExamResult extends EditRecord
{
    protected static string $resource = ExamResultResource::class;

    public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string
    {
        return "Modifier l'inscription";
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Modification de resultat')
            ->body("Vous pouvez maintenant continuer à modifier des résultats d'examen.");
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Supprimer')
                ->icon('heroicon-o-trash'),
        ];
    }
}
