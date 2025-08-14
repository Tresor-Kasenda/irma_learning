<?php

namespace App\Filament\Resources\UserProgressResource\Pages;

use App\Filament\Resources\UserProgressResource;
use App\Models\Chapter;
use App\Models\Section;
use Filament\Actions;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUserProgress extends ViewRecord
{
    protected static string $resource = UserProgressResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfoSection::make('Informations Générales')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Utilisateur'),
                        TextEntry::make('user.email')
                            ->label('Email'),
                        TextEntry::make('trackable_type')
                            ->label('Type d\'élément')
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'App\\Models\\Chapter' => 'Chapitre',
                                'App\\Models\\Section' => 'Section',
                                default => $state,
                            }),
                        TextEntry::make('trackable.title')
                            ->label('Titre de l\'élément'),
                    ])
                    ->columns(2),

                InfoSection::make('Statut et Progression')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'not_started' => 'secondary',
                                'in_progress' => 'warning',
                                'completed' => 'success',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'not_started' => 'Non commencé',
                                'in_progress' => 'En cours',
                                'completed' => 'Complété',
                                default => $state,
                            }),
                        TextEntry::make('progress_percentage')
                            ->label('Progression')
                            ->color('primary'),
                        TextEntry::make('time_spent')
                            ->label('Temps passé')
                            ->suffix(' minutes'),
                    ])
                    ->columns(2),

                InfoSection::make('Suivi Temporel')
                    ->schema([
                        TextEntry::make('started_at')
                            ->label('Commencé le')
                            ->dateTime()
                            ->placeholder('Non commencé'),
                        TextEntry::make('completed_at')
                            ->label('Complété le')
                            ->dateTime()
                            ->placeholder('Non complété'),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Mis à jour le')
                            ->dateTime(),
                    ])
                    ->columns(2),

                InfoSection::make('Contexte de Formation')
                    ->schema([
                        TextEntry::make('formation_info')
                            ->label('Formation')
                            ->getStateUsing(function ($record) {
                                if ($record->trackable instanceof Chapter) {
                                    $formation = $record->trackable->section->module->formation ?? null;
                                    return $formation ? $formation->title : 'N/A';
                                }
                                if ($record->trackable instanceof Section) {
                                    $formation = $record->trackable->module->formation ?? null;
                                    return $formation ? $formation->title : 'N/A';
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('module_info')
                            ->label('Module')
                            ->getStateUsing(function ($record) {
                                if ($record->trackable instanceof Chapter) {
                                    return $record->trackable->section->module->title ?? 'N/A';
                                }
                                if ($record->trackable instanceof Section) {
                                    return $record->trackable->module->title ?? 'N/A';
                                }
                                return 'N/A';
                            }),
                        TextEntry::make('section_info')
                            ->label('Section')
                            ->getStateUsing(function ($record) {
                                if ($record->trackable instanceof Chapter) {
                                    return $record->trackable->section->title ?? 'N/A';
                                }
                                return 'N/A';
                            })
                            ->visible(fn($record) => $record->trackable instanceof Chapter),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

}
