<?php

namespace App\Filament\Resources;

use App\Enums\QuestionTypeEnum;
use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationLabel = 'Questions';

    protected static ?string $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Question')
                    ->schema([
                        Forms\Components\Select::make('exam_id')
                            ->label('Examen')
                            ->relationship('exam', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('title')
                                    ->label('Titre de l\'examen')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description'),
                            ]),

                        Forms\Components\Select::make('question_type')
                            ->label('Type de question')
                            ->options(QuestionTypeEnum::class)
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\RichEditor::make('question_text')
                            ->label('Texte de la question')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('explanation')
                            ->label('Explication (optionnelle)')
                            ->helperText('Explication affichée après la réponse')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('points')
                            ->label('Points attribués')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        Forms\Components\TextInput::make('order_position')
                            ->label('Position dans l\'examen')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        Forms\Components\Toggle::make('is_required')
                            ->label('Question obligatoire')
                            ->default(true),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Options de réponse')
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('option_text')
                                    ->label('Texte de l\'option')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\Toggle::make('is_correct')
                                    ->label('Réponse correcte')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('order_position')
                                    ->label('Position')
                                    ->numeric()
                                    ->default(1)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->reorderable('order_position')
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['option_text'] ?? null)
                            ->addActionLabel('Ajouter une option')
                            ->minItems(2)
                            ->maxItems(10)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(Forms\Get $get): bool => in_array($get('question_type'), ['multiple_choice', 'single_choice'])
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Examen')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('Question')
                    ->limit(60)
                    ->tooltip(fn($record) => $record->question_text)
                    ->html()
                    ->searchable(),

                Tables\Columns\TextColumn::make('question_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'single_choice' => 'success',
                        'multiple_choice' => 'info',
                        'true_false' => 'warning',
                        'short_answer' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_position')
                    ->label('Position')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('options_count')
                    ->label('Options')
                    ->counts('options')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Obligatoire')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('exam')
                    ->label('Examen')
                    ->relationship('exam', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('question_type')
                    ->label('Type de question')
                    ->options(QuestionTypeEnum::class),

                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('Question obligatoire'),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\OptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'view' => Pages\ViewQuestion::route('/{record}'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['exam', 'options']);
    }
}
