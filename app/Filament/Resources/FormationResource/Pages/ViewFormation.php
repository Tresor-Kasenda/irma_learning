<?php

namespace App\Filament\Resources\FormationResource\Pages;

use App\Enums\FormationLevelEnum;
use App\Filament\Resources\FormationResource;
use App\Models\Formation;
use Filament\Actions;
use Filament\Infolists\Components\ImageEntry;
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
                        ImageEntry::make('image')
                            ->size('xl')
                            ->label('Image'),
                        TextEntry::make('short_description')
                            ->label('Description courte')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('Contenu')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Détails de formation')
                    ->schema([
                        TextEntry::make('price')
                            ->label('Prix')
                            ->money('EUR'),
                        TextEntry::make('duration_hours')
                            ->label('Durée (heures)')
                            ->suffix(' h'),
                        TextEntry::make('difficulty_level')
                            ->label('Niveau de difficulté')
                            ->badge()
                            ->formatStateUsing(fn(FormationLevelEnum $state): string => $state->getLabel())
                            ->color(fn(FormationLevelEnum $state): string => match ($state) {
                                FormationLevelEnum::BEGINNER => 'success',
                                FormationLevelEnum::INTERMEDIATE => 'warning',
                                FormationLevelEnum::ADVANCED => 'danger',
                            }),
                        TextEntry::make('certification_threshold')
                            ->label('Seuil de certification')
                            ->suffix('%'),
                        TextEntry::make('language')
                            ->label('Langue'),
                        TextEntry::make('tags')
                            ->label('Tags')
                            ->badge()
                            ->separator(',')
                            ->getStateUsing(fn(Formation $record): array => is_string($record->tags) ? json_decode($record->tags, true) ?? [] : ($record->tags ?? [])
                            ),
                    ])
                    ->columns(3),

                Section::make('Statut')
                    ->schema([
                        TextEntry::make('is_active')
                            ->label('Actif')
                            ->badge()
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Actif' : 'Inactif')
                            ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('is_featured')
                            ->label('Mis en avant')
                            ->badge()
                            ->formatStateUsing(fn(bool $state): string => $state ? 'Oui' : 'Non')
                            ->color(fn(bool $state): string => $state ? 'warning' : 'gray'),
                        TextEntry::make('created_by')
                            ->label('Créé par')
                            ->getStateUsing(fn(Formation $record): string => $record->creator->name ?? 'N/A'),
                    ])
                    ->columns(3),

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
