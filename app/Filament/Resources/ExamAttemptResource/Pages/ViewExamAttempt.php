<?php

namespace App\Filament\Resources\ExamAttemptResource\Pages;

use App\Filament\Resources\ExamAttemptResource;
use App\Models\ExamAttempt;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewExamAttempt extends ViewRecord
{
    protected static string $resource = ExamAttemptResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations générales')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Étudiant'),
                        Infolists\Components\TextEntry::make('exam.title')
                            ->label('Examen'),
                        Infolists\Components\TextEntry::make('attempt_number')
                            ->label('Tentative numéro'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Résultats')
                    ->schema([
                        Infolists\Components\TextEntry::make('score')
                            ->label('Score obtenu'),
                        Infolists\Components\TextEntry::make('max_score')
                            ->label('Score maximum'),
                        Infolists\Components\TextEntry::make('percentage')
                            ->label('Pourcentage (%)')
                            ->numeric(decimalPlaces: 2),
                        Infolists\Components\TextEntry::make('passing_status')
                            ->label('Résultat')
                            ->getStateUsing(fn(ExamAttempt $record): string => $record->isPassed() ? 'Réussi' : 'Échoué'
                            )
                            ->badge()
                            ->color(fn(ExamAttempt $record): string => $record->isPassed() ? 'success' : 'danger'
                            ),
                        Infolists\Components\TextEntry::make('time_taken')
                            ->label('Temps pris')
                            ->getStateUsing(fn($record): string => $record->time_taken ? gmdate('H:i:s', $record->time_taken) : 'N/A'
                            ),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Chronologie')
                    ->schema([
                        Infolists\Components\TextEntry::make('started_at')
                            ->label('Commencé le')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Terminé le')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('duration')
                            ->label('Durée totale')
                            ->getStateUsing(function (ExamAttempt $record): string {
                                if (!$record->started_at || !$record->completed_at) {
                                    return 'N/A';
                                }
                                $duration = $record->started_at->diffInSeconds($record->completed_at);
                                return gmdate('H:i:s', $duration);
                            }),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
