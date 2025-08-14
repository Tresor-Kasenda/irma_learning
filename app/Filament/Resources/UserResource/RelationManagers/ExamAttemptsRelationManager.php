<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\ExamAttemptEnum;
use App\Models\ExamAttempt;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExamAttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'examAttempts';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $icon = 'heroicon-o-clipboard-document-list';

    protected static ?string $title = 'Tentatives d\'examens';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

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

                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Examen')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('exam.examable_type')
                    ->label('Type')
                    ->getStateUsing(fn($record) => match ($record->exam->examable_type) {
                        'App\Models\Formation' => 'Formation',
                        'App\Models\Module' => 'Module',
                        'App\Models\Section' => 'Section',
                        'App\Models\Chapter' => 'Chapitre',
                        default => 'Inconnu',
                    })
                    ->badge(),

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
                    ->getStateUsing(fn(ExamAttempt $record) => $record->score . '/' . $record->max_score)
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('Pourcentage')
                    ->suffix('%')
                    ->alignCenter()
                    ->color(fn($state, ExamAttempt $record) => $state >= $record->exam->passing_score ? 'success' : 'danger')
                    ->weight(fn($state, ExamAttempt $record) => $state >= $record->exam->passing_score ? 'bold' : 'normal'),

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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ExamAttemptEnum::class)
                    ->multiple(),

                Tables\Filters\Filter::make('passed')
                    ->label('Réussi')
                    ->query(fn($query) => $query->whereColumn('percentage', '>=', 'exams.passing_score'))
                    ->toggle(),

                Tables\Filters\Filter::make('failed')
                    ->label('Échoué')
                    ->query(fn($query) => $query->where('status', 'completed')
                        ->whereColumn('percentage', '<', 'exams.passing_score'))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Voir')
                        ->icon('heroicon-o-eye')
                        ->color('info'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),

                    Tables\Actions\Action::make('view_answers')
                        ->label('Voir réponses')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->visible(fn(ExamAttempt $record) => $record->status === ExamAttemptEnum::COMPLETED)
                        //->url(fn(ExamAttempt $record) => route('filament.admin.resources.exam-attempts.view', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('reset_attempt')
                        ->label('Réinitialiser')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn(ExamAttempt $record) => $record->status === ExamAttemptEnum::COMPLETED)
                        ->requiresConfirmation()
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
