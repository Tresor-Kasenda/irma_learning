<?php

namespace App\Filament\Resources\ExamResource\RelationManagers;

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $recordTitleAttribute = 'question_text';

    protected static ?string $title = 'Questions';

    protected static ?string $pluralTitle = 'Questions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Question')
                    ->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->columns([
                Tables\Columns\TextColumn::make('order_position')
                    ->label('Ordre')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('Question')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(Model $record) => $record->question_text),

                Tables\Columns\TextColumn::make('question_type')
                    ->label('Type')
                    ->formatStateUsing(fn($state) => $state->getLabel())
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        QuestionTypeEnum::SINGLE_CHOICE => 'success',
                        QuestionTypeEnum::MULTIPLE_CHOICE => 'info',
                        QuestionTypeEnum::TRUE_FALSE => 'warning',
                        QuestionTypeEnum::ESSAY => 'gray',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('options_count')
                    ->label('Options')
                    ->counts('options')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Obligatoire')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('question_type')
                    ->label('Type de question')
                    ->options(collect(QuestionTypeEnum::cases())->mapWithKeys(fn($type) => [$type->value => $type->getLabel()]))
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('Obligatoire')
                    ->boolean()
                    ->trueLabel('Obligatoires seulement')
                    ->falseLabel('Optionnelles seulement')
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter une question')
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

                    Tables\Actions\Action::make('preview')
                        ->label('Aperçu')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->slideOver()
                        ->modalContent(fn(Question $record) => view('filament.resources.question.preview', ['question' => $record]))
                        ->modalWidth('3xl'),

                    Tables\Actions\Action::make('duplicate')
                        ->label('Dupliquer')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->action(function (Question $record) {
                            $newQuestion = $record->replicate(['options_count', 'answers_count']);
                            $newQuestion->question_text = $record->question_text . ' (Copie)';
                            $newQuestion->order_position = Question::query()
                                    ->where('exam_id', '=', $record->exam_id)
                                    ->max('order_position') + 1;
                            $newQuestion->save();

                            foreach ($record->options as $option) {
                                $newOption = $option->replicate();
                                $newOption->question_id = $newQuestion->id;
                                $newOption->save();
                            }

                            $this->mountedTableActionRecord = $newQuestion->getKey();
                        })
                        ->successNotificationTitle('Question dupliquée avec succès'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('make_required')
                        ->label('Rendre obligatoire')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->action(fn(Collection $records) => $records->each->update(['is_required' => true])),
                    Tables\Actions\BulkAction::make('make_optional')
                        ->label('Rendre optionnel')
                        ->icon('heroicon-o-minus-circle')
                        ->color('gray')
                        ->action(fn(Collection $records) => $records->each->update(['is_required' => false])),
                ]),
            ])
            ->defaultSort('order_position')
            ->reorderable('order_position');
    }
}
