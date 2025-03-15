<?php

namespace App\Filament\Resources\ExamFinalResource\Pages;

use App\Filament\Resources\ExamFinalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamFinal extends EditRecord
{
    protected static string $resource = ExamFinalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
