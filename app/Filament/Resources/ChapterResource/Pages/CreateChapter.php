<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Storage;

class CreateChapter extends CreateRecord
{
    protected static string $resource = ChapterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour')
                ->icon('heroicon-o-arrow-left')
                ->url(ChapterResource::getUrl('index')),
        ];
    }
}
