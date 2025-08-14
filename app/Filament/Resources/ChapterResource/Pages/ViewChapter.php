<?php

namespace App\Filament\Resources\ChapterResource\Pages;

use App\Filament\Resources\ChapterResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewChapter extends ViewRecord
{
    protected static string $resource = ChapterResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations du chapitre')
                    ->schema([
                        TextEntry::make('section.module.formation.title')
                            ->label('Formation'),
                        TextEntry::make('section.module.title')
                            ->label('Module'),
                        TextEntry::make('section.title')
                            ->label('Section'),
                        TextEntry::make('title')
                            ->label('Titre'),
                        TextEntry::make('content_type')
                            ->label('Type de contenu')
                            ->badge(),
                        TextEntry::make('order_position')
                            ->label('Position'),
                        TextEntry::make('estimated_duration')
                            ->label('Durée estimée (minutes)'),
                        IconEntry::make('is_free')
                            ->label('Gratuit')
                            ->boolean(),
                        IconEntry::make('is_active')
                            ->label('Actif')
                            ->boolean(),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Contenu')
                    ->schema([
                        TextEntry::make('content')
                            ->label('Contenu principal')
                            ->html()
                            ->columnSpanFull(),
                        KeyValueEntry::make('metadata')
                            ->label('Métadonnées')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(ChapterResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }

}
