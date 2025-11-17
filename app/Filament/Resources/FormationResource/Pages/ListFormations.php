<?php

namespace App\Filament\Resources\FormationResource\Pages;

use App\Filament\Resources\FormationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormations extends ListRecords
{
    protected static string $resource = FormationResource::class;

    protected static ?string $title = "Liste des formations";

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une formation')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
