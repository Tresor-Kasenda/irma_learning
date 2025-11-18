<?php

namespace App\Filament\Resources\ExamAttemptResource\RelationManagers;

use App\Models\UserAnswer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UserAnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'userAnswers';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Question')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->question->question_text),

                Tables\Columns\TextColumn::make('question.question_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'single_choice' => 'success',
                        'multiple_choice' => 'info',
                        'true_false' => 'warning',
                        'short_answer' => 'gray',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('selectedOption.option_text')
                    ->label('Réponse choisie')
                    ->limit(30)
                    ->placeholder('Réponse libre')
                    ->tooltip(fn($record) => $record->selectedOption?->option_text ?? $record->answer_text),

                Tables\Columns\TextColumn::make('answer_text')
                    ->label('Réponse texte')
                    ->limit(30)
                    ->placeholder('Pas de réponse texte')
                    ->tooltip(fn($record) => $record->answer_text)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_correct')
                    ->label('Correct')
                    ->boolean()
                    ->alignCenter()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('points_earned')
                    ->label('Points')
                    ->getStateUsing(fn(UserAnswer $record) => $record->points_earned . '/' . $record->question->points)
                    ->alignCenter()
                    ->color(fn(UserAnswer $record) => $record->is_correct ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('question.points')
                    ->label('Points max')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('feedback')
                    ->label('Feedback')
                    ->limit(30)
                    ->placeholder('Aucun feedback')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Répondu le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_correct')
                    ->label('Réponse correcte')
                    ->boolean()
                    ->trueLabel('Correctes seulement')
                    ->falseLabel('Incorrectes seulement')
                    ->native(false),

                Tables\Filters\SelectFilter::make('question.question_type')
                    ->label('Type de question')
                    ->options([
                        'single_choice' => 'Choix unique',
                        'multiple_choice' => 'Choix multiple',
                        'true_false' => 'Vrai/Faux',
                        'short_answer' => 'Réponse courte',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('has_feedback')
                    ->label('Avec feedback')
                    ->query(fn($query) => $query->whereNotNull('feedback'))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('add_feedback')
                    ->label('Ajouter feedback')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->form([
                        Forms\Components\Textarea::make('feedback')
                            ->label('Feedback personnalisé')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data, UserAnswer $record) {
                        $record->update(['feedback' => $data['feedback']]);
                    })
                    ->successNotificationTitle('Feedback ajouté'),

                Tables\Actions\Action::make('recalculate_points')
                    ->label('Recalculer points')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (UserAnswer $record) {
                        $record->checkCorrectness();
                    })
                    ->successNotificationTitle('Points recalculés'),

                Tables\Actions\Action::make('view_question')
                    ->label('Voir question')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(function (UserAnswer $record) {
                        return view('filament.resources.user-answer.question-modal', [
                            'question' => $record->question,
                            'answer' => $record,
                        ]);
                    })
                    ->modalWidth('4xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('add_bulk_feedback')
                        ->label('Ajouter feedback')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('info')
                        ->form([
                            Forms\Components\Textarea::make('feedback')
                                ->label('Feedback à appliquer')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (array $data, Collection $records) {
                            $records->each->update(['feedback' => $data['feedback']]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('recalculate_all_points')
                        ->label('Recalculer tous les points')
                        ->icon('heroicon-o-calculator')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(fn(UserAnswer $answer) => $answer->checkCorrectness());
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('export_answers')
                        ->label('Exporter réponses')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (Collection $records) {
                            return response()->streamDownload(function () use ($records) {
                                $csv = "Question,Type,Réponse choisie,Réponse texte,Correct,Points obtenus,Points max,Feedback\n";
                                foreach ($records as $record) {
                                    $csv .= implode(',', [
                                            '"' . str_replace('"', '""', $record->question->question_text) . '"',
                                            $record->question->question_type,
                                            '"' . str_replace('"', '""', $record->selectedOption?->option_text ?? '') . '"',
                                            '"' . str_replace('"', '""', $record->answer_text ?? '') . '"',
                                            $record->is_correct ? 'Oui' : 'Non',
                                            $record->points_earned,
                                            $record->question->points,
                                            '"' . str_replace('"', '""', $record->feedback ?? '') . '"',
                                        ]) . "\n";
                                }
                                echo $csv;
                            }, 'user-answers-' . now()->format('Y-m-d') . '.csv');
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Réponse de l\'utilisateur')
                    ->schema([
                        Forms\Components\Select::make('question_id')
                            ->label('Question')
                            ->relationship('question', 'question_text')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('selected_option_id')
                            ->label('Option sélectionnée')
                            ->relationship('selectedOption', 'option_text')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('answer_text')
                            ->label('Réponse texte')
                            ->helperText('Pour les questions à réponse libre')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_correct')
                                    ->label('Réponse correcte')
                                    ->disabled()
                                    ->helperText('Calculé automatiquement'),

                                Forms\Components\TextInput::make('points_earned')
                                    ->label('Points obtenus')
                                    ->numeric()
                                    ->minValue(0)
                                    ->disabled()
                                    ->helperText('Calculé automatiquement'),
                            ]),

                        Forms\Components\Textarea::make('feedback')
                            ->label('Feedback personnalisé')
                            ->helperText('Commentaire pour cette réponse spécifique')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
