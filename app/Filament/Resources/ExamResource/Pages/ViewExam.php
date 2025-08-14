<?php

namespace App\Filament\Resources\ExamResource\Pages;

use App\Filament\Resources\ChapterResource;
use App\Filament\Resources\ExamResource;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Section;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Informations générales')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Titre'),
                        TextEntry::make('examable_type')
                            ->label('Type d\'élément')
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                Formation::class => 'Formation',
                                Module::class => 'Module',
                                Section::class => 'Section',
                                Chapter::class => 'Chapitre',
                                default => 'Inconnu',
                            })
                            ->badge(),
                        TextEntry::make('examable.title')
                            ->label('Élément associé'),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                        TextEntry::make('instructions')
                            ->label('Instructions')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Configuration')
                    ->schema([
                        TextEntry::make('duration_minutes')
                            ->label('Durée (minutes)'),
                        TextEntry::make('passing_score')
                            ->label('Score minimum (%)'),
                        TextEntry::make('max_attempts')
                            ->label('Tentatives maximum'),
                        TextEntry::make('available_from')
                            ->label('Disponible à partir de')
                            ->dateTime(),
                        TextEntry::make('available_until')
                            ->label('Disponible jusqu\'au')
                            ->dateTime(),
                        IconEntry::make('randomize_questions')
                            ->label('Questions mélangées')
                            ->boolean(),
                        IconEntry::make('show_results_immediately')
                            ->label('Résultats immédiats')
                            ->boolean(),
                        IconEntry::make('is_active')
                            ->label('Actif')
                            ->boolean(),
                    ])
                    ->columns(3),

                Section::make('Statistiques')
                    ->schema([
                        TextEntry::make('questions_count')
                            ->label('Nombre de questions')
                            ->getStateUsing(fn(Exam $record): int => $record->questions()->count()),

                        TextEntry::make('attempts_count')
                            ->label('Nombre de tentatives')
                            ->getStateUsing(fn(Exam $record): int => $record->attempts()->count()),

                        TextEntry::make('success_rate')
                            ->label('Taux de réussite (%)')
                            ->getStateUsing(function (Exam $record): string {
                                $totalAttempts = $record->attempts()->count();
                                if ($totalAttempts === 0) {
                                    return 'N/A';
                                }
                                $successfulAttempts = $record->attempts()
                                    ->where('score', '>=', $record->passing_score)
                                    ->count();
                                return round(($successfulAttempts / $totalAttempts) * 100, 1) . '%';
                            }),
                    ])
                    ->columns(3),
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
