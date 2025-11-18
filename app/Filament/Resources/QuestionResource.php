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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationLabel = 'Questions';

    protected static ?string $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('exam.title')
                    ->label('Examen')
                    ->collapsible()
            ])
            ->defaultGroup('exam.title')
            ->columns([
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Examen')
                    ->sortable()
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('Question')
                    ->limit(60)
                    ->tooltip(fn($record) => strip_tags($record->question_text))
                    ->html()
                    ->searchable(),

                Tables\Columns\TextColumn::make('question_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->getLabel())
                    ->color(fn(QuestionTypeEnum $state): string => match ($state) {
                        QuestionTypeEnum::SINGLE_CHOICE => 'success',
                        QuestionTypeEnum::MULTIPLE_CHOICE => 'info',
                        QuestionTypeEnum::TRUE_FALSE => 'warning',
                        QuestionTypeEnum::ESSAY => 'gray',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_position')
                    ->label('Position')
                    ->numeric()
                    ->sortable(),
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
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Voir')
                        ->icon('heroicon-o-eye'),
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Question')
                    ->schema([
                        Forms\Components\Select::make('exam_id')
                            ->label('Examen')
                            ->relationship('exam', 'title')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if (!$state) {
                                    $set('order_position', 1);
                                    return;
                                }

                                $nextPosition = Question::query()
                                    ->where('exam_id', $state)
                                    ->max('order_position');

                                $set('order_position', ($nextPosition ?? 0) + 1);
                            })
                            ->required(),
                        Forms\Components\TextInput::make('question_text')
                            ->label('Texte de la question')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('question_type')
                            ->label('Type de question')
                            ->options(collect(QuestionTypeEnum::cases())->mapWithKeys(fn($type) => [$type->value => $type->getLabel()]))
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('points')
                                    ->label('Points')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),

                                Forms\Components\Toggle::make('is_required')
                                    ->label('Question obligatoire')
                                    ->inline(false)
                                    ->default(true),
                            ]),

                        Forms\Components\TextInput::make('order_position')
                            ->label('Position')
                            ->numeric()
                            ->default(1)
                            ->disabled()
                            ->dehydrated(true)
                            ->required(),

                        Forms\Components\Textarea::make('explanation')
                            ->label('Explication (optionnelle)')
                            ->helperText('Explication affichée après la réponse')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Options de réponse')
                    ->schema([
                        Forms\Components\Repeater::make('options')
                            ->label('Options')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('option_text')
                                    ->label('Texte de l\'option')
                                    ->required(),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('order_position')
                                            ->label('Position')
                                            ->numeric()
                                            ->default(function (Forms\Get $get) {
                                                $options = collect($get('../../options') ?? []);
                                                return $options->count() > 0 ? $options->max('order_position') + 1 : 1;
                                            })
                                            ->disabled()
                                            ->dehydrated(),
                                        Forms\Components\Toggle::make('is_correct')
                                            ->label('Réponse correcte')
                                            ->inline(false)
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                if ($get('../../question_type') === QuestionTypeEnum::SINGLE_CHOICE->value && $state) {
                                                    $options = collect($get('../../options') ?? []);

                                                    $options->each(function ($option, $index) use ($set, $get) {
                                                        if ($index !== $get('../../_index')) {
                                                            $set("../../options.{$index}.is_correct", false);
                                                        }
                                                    });
                                                }
                                            })
                                            ->visible(fn(Forms\Get $get) => in_array($get('../../question_type'), [
                                                QuestionTypeEnum::SINGLE_CHOICE->value,
                                                QuestionTypeEnum::MULTIPLE_CHOICE->value
                                            ]))
                                            ->default(false),
                                    ]),
                            ])
                            ->minItems(4)
                            ->maxItems(5)
                            ->defaultItems(4)
                            ->itemLabel(fn(array $state): ?string => $state['option_text'] ?? 'Nouvelle option')
                            ->collapsed()
                            ->cloneable()
                            ->reorderable()
                            ->columnSpanFull()
                            ->visible(fn(Forms\Get $get) => in_array($get('question_type'), [
                                QuestionTypeEnum::SINGLE_CHOICE->value,
                                QuestionTypeEnum::MULTIPLE_CHOICE->value,
                            ])),
                    ])
                    ->visible(fn(Forms\Get $get) => in_array($get('question_type'), ['single_choice', 'multiple_choice'])),
            ]);
    }
}
