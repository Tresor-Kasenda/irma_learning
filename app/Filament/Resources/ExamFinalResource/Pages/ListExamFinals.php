<?php

namespace App\Filament\Resources\ExamFinalResource\Pages;

use App\Filament\Resources\ExamFinalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamFinals extends ListRecords
{
    protected static string $resource = ExamFinalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
