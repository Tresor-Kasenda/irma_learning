<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExamResource\Pages;

use App\Enums\ExamAttemptEnum;
use App\Filament\Resources\ExamResource;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Section;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

final class ViewExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Informations générales')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Titre'),

                        TextEntry::make('examable_type')
                            ->label('Type d\'élément')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                Formation::class => 'Formation',
                                Section::class => 'Section',
                                Chapter::class => 'Chapitre',
                                default => 'Inconnu',
                            })
                            ->badge()
                            ->color(fn ($state): string => match ($state) {
                                Formation::class => 'success',
                                Section::class => 'warning',
                                Chapter::class => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('examable.title')
                            ->label('Élément associé'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),

                        TextEntry::make('instructions')
                            ->label('Instructions')
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                \Filament\Schemas\Components\Section::make('Configuration de l\'examen')
                    ->schema([
                        TextEntry::make('duration_minutes')
                            ->label('Durée (minutes)')
                            ->numeric(),

                        TextEntry::make('passing_score')
                            ->label('Score minimum (%)')
                            ->numeric(),

                        TextEntry::make('max_attempts')
                            ->label('Tentatives maximum')
                            ->numeric()
                            ->helperText('0 pour tentatives illimitées'),

                        TextEntry::make('available_from')
                            ->label('Disponible à partir de')
                            ->dateTime(),

                        TextEntry::make('available_until')
                            ->label('Disponible jusqu\'au')
                            ->dateTime(),
                    ])
                    ->columns(3),

                \Filament\Schemas\Components\Section::make('Options')
                    ->schema([
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

                \Filament\Schemas\Components\Section::make('Statistiques')
                    ->schema([
                        TextEntry::make('questions_count')
                            ->label('Nombre de questions')
                            ->getStateUsing(fn (Exam $record): int => $record->questions()->count()),

                        TextEntry::make('attempts_count')
                            ->label('Nombre de tentatives')
                            ->getStateUsing(fn (Exam $record): int => $record->attempts()->count()),

                        TextEntry::make('success_rate')
                            ->label('Taux de réussite (%)')
                            ->getStateUsing(function (Exam $record): string {
                                $totalAttempts = $record->attempts()->count();
                                if ($totalAttempts === 0) {
                                    return 'N/A';
                                }
                                $successfulAttempts = $record->attempts()
                                    ->where('status', ExamAttemptEnum::COMPLETED->value)
                                    ->where('percentage', '>=', $record->getPassingScore())
                                    ->count();

                                return round(($successfulAttempts / $totalAttempts) * 100, 1).'%';
                            }),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour aux examens')
                ->url(ExamResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
