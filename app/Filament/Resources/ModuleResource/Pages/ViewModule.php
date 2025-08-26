<?php

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Filament\Resources\ModuleResource;
use App\Models\Module;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewModule extends ViewRecord
{
    protected static string $resource = ModuleResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations du module')
                    ->schema([
                        TextEntry::make('formation.title')
                            ->label('Formation'),
                        TextEntry::make('title')
                            ->label('Titre'),
                        TextEntry::make('is_active')
                            ->label('Actif')
                            ->badge()
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Actif' : 'Inactif')
                            ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('estimated_duration')
                            ->label('Durée estimée (minutes)')
                            ->suffix(' min'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        TextEntry::make('content')
                            ->label('Contenu')
                            ->html()
                            ->columnSpanFull()
                            ->placeholder('Aucun contenu disponible'),
                    ])
                    ->columns(2),

                Section::make('Statistiques')
                    ->schema([
                        TextEntry::make('sections_count')
                            ->label('Nombre de sections')
                            ->getStateUsing(fn(Module $record): int => $record->sections()->count()),

                        TextEntry::make('chapters_count')
                            ->label('Nombre de chapitres')
                            ->getStateUsing(fn(Module $record): int => $record->getChaptersCount()),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(ModuleResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
