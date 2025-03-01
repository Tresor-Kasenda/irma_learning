<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamResultResource\Pages;

use App\Filament\Resources\ExamResultResource;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

final class CreateExamResult extends CreateRecord
{
    protected static string $resource = ExamResultResource::class;

    public function getHeading(): \Illuminate\Contracts\Support\Htmlable|string
    {
        return "Créer un résultat d'examen";
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['evaluated_by'] = Auth::user()->id;
        $data['published_at'] = Carbon::now();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title("Création du résultat d'examen")
            ->body('Vous pouvez maintenant continuer à ajouter des résultats d\'examen.');
    }
}
