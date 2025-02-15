<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

final class ViewEvents extends ViewRecord
{
    protected static string $resource = EventResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make('Informations de base')
                        ->schema([
                            TextEntry::make('type.title')
                                ->badge()
                                ->label('Type d\'événement'),
                            TextEntry::make('title')
                                ->label('Titre de l\'événement'),
                            TextEntry::make('date')
                                ->label('Date de l\'événement')
                                ->dateTime(
                                    'd/m/Y',
                                ),
                            TextEntry::make('tarif_membre')
                                ->label('Tarif membre')
                                ->color('green')
                                ->money('USD'),
                            TextEntry::make('tarif_non_membre')
                                ->label('Tarif non membre')
                                ->color('red')
                                ->money('USD'),
                            TextEntry::make('heure_debut')
                                ->label('Heure de début'),
                            TextEntry::make('heure_fin')
                                ->label('Heure de fin'),

                            TextEntry::make('description')
                                ->html()
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                ])
                    ->columnSpan(['lg' => 2]),
                Split::make([
                    Section::make('Image')
                        ->description()
                        ->schema([
                            ImageEntry::make('image')
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }
}
