<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamResultResource\Pages;

use App\Filament\Resources\ExamResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListExamResults extends ListRecords
{
    protected static string $resource = ExamResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label("Ajouter un résultat d'examen")
                ->icon('heroicon-o-plus-circle'),

        ];
    }
}
