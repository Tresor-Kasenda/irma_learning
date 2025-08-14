<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionOptionResource\Pages;
use App\Models\Question;
use App\Models\QuestionOption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Str;

class QuestionOptionResource extends Resource
{
    protected static ?string $model = QuestionOption::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationLabel = 'Options de question';

    protected static ?string $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Option de réponse')
                    ->schema([
                        Forms\Components\Select::make('question_id')
                            ->label('Question')
                            ->relationship('question', 'question_text')
                            ->getOptionLabelFromRecordUsing(fn(Question $record) => Str::limit(strip_tags($record->question_text), 60)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Textarea::make('option_text')
                            ->label('Texte de l\'option')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('explanation')
                            ->label('Explication (optionnelle)')
                            ->helperText('Explication affichée si cette option est sélectionnée')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('is_correct')
                            ->label('Réponse correcte')
                            ->helperText('Marquer cette option comme la bonne réponse'),

                        Forms\Components\TextInput::make('order_position')
                            ->label('Position dans la liste')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question.exam.title')
                    ->label('Examen')
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('question.question_text')
                    ->label('Question')
                    ->limit(40)
                    ->tooltip(fn($record) => strip_tags($record->question->question_text))
                    ->html()
                    ->searchable(),

                Tables\Columns\TextColumn::make('option_text')
                    ->label('Option')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->option_text)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_correct')
                    ->label('Correcte')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('order_position')
                    ->label('Position')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_answers_count')
                    ->label('Sélections')
                    ->counts('userAnswers')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('question')
                    ->label('Question')
                    ->relationship('question', 'question_text')
                    ->getOptionLabelFromRecordUsing(fn(Question $record) => Str::limit(strip_tags($record->question_text), 60)
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_correct')
                    ->label('Réponse correcte')
                    ->boolean()
                    ->trueLabel('Correctes uniquement')
                    ->falseLabel('Incorrectes uniquement'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order_position', 'asc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations générales')
                    ->schema([
                        Infolists\Components\TextEntry::make('question.exam.title')
                            ->label('Examen'),
                        Infolists\Components\TextEntry::make('question.question_text')
                            ->label('Question')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Option de réponse')
                    ->schema([
                        Infolists\Components\TextEntry::make('option_text')
                            ->label('Texte de l\'option')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('explanation')
                            ->label('Explication')
                            ->columnSpanFull()
                            ->placeholder('Aucune explication fournie'),

                        Infolists\Components\IconEntry::make('is_correct')
                            ->label('Réponse correcte')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('order_position')
                            ->label('Position'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Statistiques')
                    ->schema([
                        Infolists\Components\TextEntry::make('user_answers_count')
                            ->label('Nombre de sélections')
                            ->getStateUsing(fn(QuestionOption $record): int => $record->userAnswers()->count()),

                        Infolists\Components\TextEntry::make('selection_rate')
                            ->label('Taux de sélection (%)')
                            ->getStateUsing(function (QuestionOption $record): string {
                                $totalAnswers = $record->question->answers()->count();
                                if ($totalAnswers === 0) {
                                    return 'N/A';
                                }
                                $selections = $record->userAnswers()->count();
                                return round(($selections / $totalAnswers) * 100, 1) . '%';
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionOptions::route('/'),
            'create' => Pages\CreateQuestionOption::route('/create'),
            'view' => Pages\ViewQuestionOption::route('/{record}'),
            'edit' => Pages\EditQuestionOption::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['question.exam']);
    }
}
