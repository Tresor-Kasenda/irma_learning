<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use App\Enums\ExamAttemptEnum;
use App\Models\ExamAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la tentative')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Utilisateur')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(ExamAttemptEnum::class)
                            ->required()
                            ->default('in_progress'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('attempt_number')
                                    ->label('Numéro de tentative')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),

                                Forms\Components\TextInput::make('score')
                                    ->label('Score obtenu')
                                    ->numeric()
                                    ->minValue(0),

                                Forms\Components\TextInput::make('max_score')
                                    ->label('Score maximum')
                                    ->numeric()
                                    ->minValue(0),
                            ]),
                        Forms\Components\TextInput::make('percentage')
                            ->label('Pourcentage')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('started_at')
                                    ->label('Commencé le')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\DateTimePicker::make('completed_at')
                                    ->label('Terminé le'),
                            ]),

                        Forms\Components\TextInput::make('time_taken')
                            ->label('Temps pris (secondes)')
                            ->numeric()
                            ->suffix('s'),
                    ]),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('attempt_number')
                    ->label('Tentative')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        ExamAttemptEnum::COMPLETED => 'success',
                        ExamAttemptEnum::IN_PROGRESS => 'warning',
                        ExamAttemptEnum::FAILED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->getStateUsing(fn(ExamAttempt $record) => $record->score ? $record->score . '/' . $record->max_score : '-')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('Pourcentage')
                    ->suffix('%')
                    ->alignCenter()
                    ->color(fn($state, ExamAttempt $record) => $state && $record->exam ?
                        ($state >= $record->exam->passing_score ? 'success' : 'danger') :
                        'gray'
                    )
                    ->weight(
                        fn($state, ExamAttempt $record) => $state && $record->exam && $state >= $record->exam->passing_score ? 'bold' : 'normal'
                    ),

                Tables\Columns\IconColumn::make('is_passed')
                    ->label('Réussi')
                    ->getStateUsing(fn(ExamAttempt $record) => $record->isPassed())
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('time_taken')
                    ->label('Durée')
                    ->getStateUsing(fn($state) => $state ? gmdate('H:i:s', $state) : '-')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Commencé')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Terminé')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('En cours'),

                Tables\Columns\TextColumn::make('user_answers_count')
                    ->label('Réponses')
                    ->counts('userAnswers')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ExamAttemptEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('user')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\Filter::make('passed')
                    ->label('Réussi')
                    ->query(fn(Builder $query) => $query->whereColumn('percentage', '>=', 'exams.passing_score'))
                    ->toggle(),

                Tables\Filters\Filter::make('failed')
                    ->label('Échoué')
                    ->query(fn(Builder $query) => $query->where('status', 'completed')
                        ->whereColumn('percentage', '<', 'exams.passing_score')
                    )
                    ->toggle(),

                Tables\Filters\Filter::make('in_progress')
                    ->label('En cours')
                    ->query(fn(Builder $query) => $query->where('status', 'in_progress'))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nouvelle tentative')
                    ->icon('heroicon-o-plus')
                    ->slideOver(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Voir')
                        ->slideOver()
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->label('Modifier')
                        ->slideOver()
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),

                    Tables\Actions\Action::make('view_answers')
                        ->label('Voir réponses')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->visible(fn(ExamAttempt $record) => $record->userAnswers()->exists())
                        ->url(fn(ExamAttempt $record) => route('filament.admin.resources.exam-attempts.view', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('complete_attempt')
                        ->label('Terminer tentative')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(ExamAttempt $record) => $record->status === ExamAttemptEnum::IN_PROGRESS)
                        ->requiresConfirmation()
                        ->modalDescription('Cette action va calculer le score final et marquer la tentative comme terminée.')
                        ->action(function (ExamAttempt $record) {
                            $record->complete();
                        })
                        ->successNotificationTitle('Tentative terminée'),

                    Tables\Actions\Action::make('reset_attempt')
                        ->label('Réinitialiser')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn(ExamAttempt $record) => $record->status === ExamAttemptEnum::COMPLETED)
                        ->requiresConfirmation()
                        ->modalDescription('Cette action va supprimer toutes les réponses et remettre la tentative en cours.')
                        ->action(function (ExamAttempt $record) {
                            $record->update([
                                'status' => ExamAttemptEnum::IN_PROGRESS,
                                'completed_at' => null,
                                'score' => null,
                                'percentage' => null,
                                'time_taken' => null,
                            ]);
                            $record->userAnswers()->delete();
                        })
                        ->successNotificationTitle('Tentative réinitialisée'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('complete_attempts')
                        ->label('Terminer les tentatives')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->where('status', ExamAttemptEnum::IN_PROGRESS)
                                ->each(fn(ExamAttempt $attempt) => $attempt->complete());
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('export_results')
                        ->label('Exporter résultats')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (Collection $records) {
                            // Logique d'export ici
                            return response()->streamDownload(function () use ($records) {
                                $csv = "ID,Utilisateur,Email,Tentative,Statut,Score,Pourcentage,Commencé,Terminé\n";
                                foreach ($records as $record) {
                                    $csv .= implode(',', [
                                            $record->id,
                                            $record->user->name,
                                            $record->user->email,
                                            $record->attempt_number,
                                            $record->status->value,
                                            $record->score . '/' . $record->max_score,
                                            $record->percentage . '%',
                                            $record->started_at?->format('Y-m-d H:i:s'),
                                            $record->completed_at?->format('Y-m-d H:i:s') ?? 'En cours',
                                        ]) . "\n";
                                }
                                echo $csv;
                            }, 'exam-attempts-' . now()->format('Y-m-d') . '.csv');
                        }),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
