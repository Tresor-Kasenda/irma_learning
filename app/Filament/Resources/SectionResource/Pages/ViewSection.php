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
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('Aucune description'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Statut')
                    ->schema([
                        Infolists\Components\TextEntry::make('is_active')
                            ->label('Active')
                            ->badge()
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Active' : 'Inactive')
                            ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                        Infolists\Components\TextEntry::make('estimated_duration')
                            ->label('Durée estimée (minutes)')
                            ->suffix(' min')
                            ->placeholder('Non définie'),
                        Infolists\Components\TextEntry::make('chapters_count')
                            ->label('Nombre de chapitres')
                            ->getStateUsing(fn(Section $record): int => $record->chapters()->count()),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(SectionResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
        ];
    }
}
