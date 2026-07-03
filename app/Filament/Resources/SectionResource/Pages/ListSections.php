<?php

declare(strict_types=1);

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListSections extends ListRecords
{
    protected static string $resource = SectionResource::class;

    protected static ?string $title = 'Liste des sections';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une section')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
