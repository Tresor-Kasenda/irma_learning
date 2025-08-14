<?php

namespace App\Filament\Resources\FormationResource\Pages;

use App\Filament\Resources\FormationResource;
use App\Models\Formation;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewFormation extends ViewRecord
{
    protected static string $resource = FormationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Titre'),
                        TextEntry::make('slug'),
                        TextEntry::make('short_description')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('Contenu')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Statistiques')
                    ->schema([
                        TextEntry::make('enrollments_count')
                            ->label('Nombre d\'inscriptions')
                            ->getStateUsing(fn(Formation $record): int => $record->getEnrollmentCount()),

                        TextEntry::make('total_chapters')
                            ->label('Nombre de chapitres')
                            ->getStateUsing(fn(Formation $record): int => $record->getTotalChaptersCount()),

                        TextEntry::make('estimated_duration')
                            ->label('Durée estimée (minutes)')
                            ->getStateUsing(fn(Formation $record): int => $record->getEstimatedDuration()),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(FormationResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
