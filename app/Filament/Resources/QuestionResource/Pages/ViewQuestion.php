<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestion extends ViewRecord
{
    protected static string $resource = QuestionResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations de la question')
                    ->schema([
                        Infolists\Components\TextEntry::make('exam.title')
                            ->label('Examen'),
                        Infolists\Components\TextEntry::make('question_type')
                            ->formatStateUsing(fn($state) => $state->getLabel())
                            ->label('Type de question')
                            ->badge(),
                        Infolists\Components\TextEntry::make('points')
                            ->label('Points'),
                        Infolists\Components\TextEntry::make('order_position')
                            ->label('Position'),
                        Infolists\Components\IconEntry::make('is_required')
                            ->label('Obligatoire')
                            ->boolean(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Contenu')
                    ->schema([
                        Infolists\Components\TextEntry::make('question_text')
                            ->label('Question')
                            ->html()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('explanation')
                            ->label('Explication')
                            ->columnSpanFull()
                            ->placeholder('Aucune explication fournie'),
                    ]),

                Infolists\Components\Section::make('Options de réponse')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('options')
                            ->schema([
                                Infolists\Components\TextEntry::make('option_text')
                                    ->label('Option'),
                                Infolists\Components\IconEntry::make('is_correct')
                                    ->label('Correcte')
                                    ->boolean(),
                                Infolists\Components\TextEntry::make('order_position')
                                    ->label('Position'),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record): bool => $record->options()->count() > 0),

                Infolists\Components\Section::make('Statistiques')
                    ->schema([
                        Infolists\Components\TextEntry::make('answers_count')
                            ->label('Nombre de réponses')
                            ->getStateUsing(fn(Question $record): int => $record->answers()->count()),

                        Infolists\Components\TextEntry::make('correct_answers_rate')
                            ->label('Taux de bonnes réponses (%)')
                            ->getStateUsing(function (Question $record): string {
                                $totalAnswers = $record->answers()->count();
                                if ($totalAnswers === 0) {
                                    return 'N/A';
                                }

                                $correctAnswers = $record->answers()
                                    ->whereHas('selectedOption', function ($query) {
                                        $query->where('is_correct', true);
                                    })
                                    ->count();

                                return round(($correctAnswers / $totalAnswers) * 100, 1) . '%';
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Retour')
                ->url(QuestionResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
