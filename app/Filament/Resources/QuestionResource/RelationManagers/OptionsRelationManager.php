<?php

namespace App\Filament\Resources\QuestionResource\RelationManagers;

use App\Models\QuestionOption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    protected static ?string $recordTitleAttribute = 'option_text';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('option_text')
            ->columns([
                Tables\Columns\TextColumn::make('order_position')
                    ->label('Ordre')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('option_text')
                    ->label('Option')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(fn($record) => $record->option_text),

                Tables\Columns\IconColumn::make('is_correct')
                    ->label('Correcte')
                    ->boolean()
                    ->alignCenter()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('user_answers_count')
                    ->label('Sélections')
                    ->counts('userAnswers')
                    ->alignCenter()
                    ->description('Nombre de fois sélectionnée'),

                Tables\Columns\TextColumn::make('success_rate')
                    ->label('Taux de réussite')
                    ->getStateUsing(function (QuestionOption $record) {
                        $totalAnswers = $record->userAnswers()->count();
                        if ($totalAnswers === 0) return 'Aucune donnée';

                        $correctAnswers = $record->userAnswers()
                            ->where('is_correct', true)
                            ->count();

                        return round(($correctAnswers / $totalAnswers) * 100, 1) . '%';
                    })
                    ->alignCenter()
                    ->color(fn($state) => match (true) {
                        str_contains($state, 'Aucune') => 'gray',
                        (float)str_replace('%', '', $state) >= 70 => 'success',
                        (float)str_replace('%', '', $state) >= 40 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('explanation')
                    ->label('Explication')
                    ->limit(30)
                    ->placeholder('Aucune explication')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
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

                Tables\Filters\Filter::make('has_explanation')
                    ->label('Avec explication')
                    ->query(fn($query) => $query->whereNotNull('explanation'))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('toggle_correct')
                    ->label(fn(QuestionOption $record) => $record->is_correct ? 'Marquer incorrect' : 'Marquer correct')
                    ->icon(fn(QuestionOption $record) => $record->is_correct ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn(QuestionOption $record) => $record->is_correct ? 'danger' : 'success')
                    ->action(function (QuestionOption $record) {
                        $record->update(['is_correct' => !$record->is_correct]);
                    })
                    ->successNotificationTitle('Statut mis à jour'),

                Tables\Actions\Action::make('view_answers')
                    ->label('Voir réponses')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn(QuestionOption $record) => $record->userAnswers()->exists())
                    ->modalContent(function (QuestionOption $record) {
                        $answers = $record->userAnswers()
                            ->with('examAttempt.user')
                            ->latest()
                            ->limit(10)
                            ->get();

                        return view('filament.resources.question-option.answers-modal', [
                            'option' => $record,
                            'answers' => $answers
                        ]);
                    })
                    ->modalWidth('4xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('mark_correct')
                        ->label('Marquer comme correct')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn(Collection $records) => $records->each->update(['is_correct' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('mark_incorrect')
                        ->label('Marquer comme incorrect')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn(Collection $records) => $records->each->update(['is_correct' => false]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('add_explanation')
                        ->label('Ajouter explication')
                        ->icon('heroicon-o-light-bulb')
                        ->color('info')
                        ->form([
                            Forms\Components\Textarea::make('explanation')
                                ->label('Explication')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (array $data, Collection $records) {
                            $records->each->update(['explanation' => $data['explanation']]);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('order_position')
            ->reorderable('order_position');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Option de réponse')
                    ->schema([
                        Forms\Components\Textarea::make('option_text')
                            ->label('Texte de l\'option')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('is_correct')
                                    ->label('Réponse correcte')
                                    ->default(false)
                                    ->helperText('Cochez si cette option est une bonne réponse'),

                                Forms\Components\TextInput::make('order_position')
                                    ->label('Position')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                            ]),

                        Forms\Components\Textarea::make('explanation')
                            ->label('Explication (optionnelle)')
                            ->helperText('Explication affichée lorsque cette option est sélectionnée')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
