<?php

namespace App\Filament\Resources\TrainingResource\Pages;

use App\Filament\Resources\TrainingResource;
use Filament\Actions;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTraining extends ViewRecord
{
    protected static string $resource = TrainingResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Informations du cours')
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Titre du cours'),
                                TextEntry::make('slug')
                                    ->label('Slug du cours'),
                                TextEntry::make('content')
                                    ->label('Introduction')
                                    ->markdown(),
                                TextEntry::make('description')
                                    ->label('Description du cours')
                                    ->html(),
                                TextEntry::make('status')
                                    ->label('Statut')
                                    ->badge()
                                    ->color(fn($state): string => match ($state) {
                                        'published' => 'success',
                                        'unpublished' => 'warning',
                                        'draft' => 'info',
                                        'archived' => 'danger',
                                        default => 'gray',
                                    })
                            ])
                            ->columns(2),
                    ])->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Photo couverture')
                            ->schema([
                                ImageEntry::make('image')
                                    ->label('Photo de couverture')
                                    ->circular(),
                            ]),
                        Section::make('Information du prix')
                            ->schema([
                                TextEntry::make('duration')
                                    ->label('Durée du cours')
                                    ->suffix(' heures'),
                                TextEntry::make('price')
                                    ->label('Prix du cours')
                                    ->money('USD'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Retour')
                ->url(TrainingResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->label('Retour')
        ];
    }
}
