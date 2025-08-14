<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ExamResource;
use App\Filament\Resources\ModuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExam extends EditRecord
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(ModuleResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
            Actions\ViewAction::make()
                ->label('Voir')
                ->icon('heroicon-o-eye')
                ->url(ModuleResource::getUrl('view', ['record' => $this->record])),
        ];
    }
}
