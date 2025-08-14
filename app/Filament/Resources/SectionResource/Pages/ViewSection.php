<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use App\Models\Section;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSection extends ViewRecord
{
    protected static string $resource = SectionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations de la section')
                    ->schema([
                        Infolists\Components\TextEntry::make('module.formation.title')
                            ->label('Formation'),
                        Infolists\Components\TextEntry::make('module.title')
                            ->label('Module'),
                        Infolists\Components\TextEntry::make('title')
                            ->label('Titre'),
                        Infolists\Components\TextEntry::make('order_position')
                            ->label('Position'),
                        Infolists\Components\TextEntry::make('estimated_duration')
                            ->label('Durée estimée (minutes)'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('content')
                            ->label('Contenu')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Statistiques')
                    ->schema([
                        Infolists\Components\TextEntry::make('chapters_count')
                            ->label('Nombre de chapitres')
                            ->getStateUsing(fn(Section $record): int => $record->chapters()->count()),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

}
