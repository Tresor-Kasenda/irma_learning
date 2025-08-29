<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionOptionResource\Pages;
use App\Models\Question;
use App\Models\QuestionOption;
use Filament\Forms;
use Filament\Forms\Form;
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
                            ->getOptionLabelFromRecordUsing(
                                fn(Question $record) => Str::limit(strip_tags($record->question_text), 60)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('option_text')
                            ->label('Texte de l\'option')
                            ->required(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Image (optionnelle)')
                            ->image()
                            ->directory('question-options')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('is_correct')
                            ->label('Réponse correcte')
                            ->helperText('Marquer cette option comme la bonne réponse'),

                        Forms\Components\TextInput::make('order_position')
                            ->label('Position')
                            ->numeric()
                            ->default(function (Forms\Get $get) {
                                $questionId = $get('question_id');
                                if (!$questionId) return 1;

                                return QuestionOption::query()
                                        ->where('question_id', $questionId)
                                        ->max('order_position') + 1;
                            })
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular(),

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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Modifier')
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('order_position', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestionOptions::route('/'),
            'create' => Pages\CreateQuestionOption::route('/create'),
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
