<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;
use App\Filament\Resources\ExamResource\RelationManagers;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ExamResource extends Resource
{
    protected static ?string $model = Exam::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Examens';

    protected static ?string $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Association')
                    ->schema([
                        Forms\Components\Select::make('examable_type')
                            ->label('Type d\'élément')
                            ->options([
                                Formation::class => 'Formation',
                                Section::class => 'Section',
                                Chapter::class => 'Chapitre',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('examable_id', null)),

                        Forms\Components\Select::make('formation_id')
                            ->label('Formation')
                            ->options(fn() => Formation::pluck('title', 'id')->toArray())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->visible(fn(Forms\Get $get) => !empty($get('examable_type')))
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('section_id', null);
                                $set('examable_id', null);
                            }),

                        Forms\Components\Select::make('section_id')
                            ->label('Section')
                            ->options(function (Forms\Get $get) {
                                $formationId = $get('formation_id');
                                if (!$formationId) {
                                    return [];
                                }

                                return Section::where('formation_id', $formationId)
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(fn(Forms\Get $get) => $get('examable_type') === Chapter::class)
                            ->visible(fn(Forms\Get $get) => $get('examable_type') === Chapter::class &&
                                !empty($get('formation_id')))
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('examable_id', null);
                            }),

                        Forms\Components\Select::make('examable_id')
                            ->label(fn(Forms\Get $get) => match ($get('examable_type')) {
                                Formation::class => 'Formation',
                                Section::class => 'Section',
                                Chapter::class => 'Chapitre',
                                default => 'Élément',
                            })
                            ->options(function (Forms\Get $get) {
                                $type = $get('examable_type');

                                if (!$type) {
                                    return [];
                                }

                                return match ($type) {
                                    Formation::class => [$get('formation_id') => Formation::find($get('formation_id'))?->title ?? ''],
                                    Section::class => Section::where('formation_id', $get('formation_id'))
                                        ->pluck('title', 'id')
                                        ->toArray(),
                                    Chapter::class => Chapter::where('section_id', $get('section_id'))
                                        ->pluck('title', 'id')
                                        ->toArray(),
                                    default => [],
                                };
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->hidden(fn(Forms\Get $get) => ($get('examable_type') === Formation::class && empty($get('formation_id'))) ||
                                ($get('examable_type') === Section::class && empty($get('formation_id'))) ||
                                ($get('examable_type') === Chapter::class && empty($get('section_id'))) ||
                                empty($get('examable_type'))),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre de l\'examen')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('instructions')
                            ->label('Instructions pour les étudiants')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Configuration de l\'examen')
                    ->schema([
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Durée en minutes')
                            ->numeric()
                            ->minValue(1)
                            ->default(60)
                            ->required(),

                        Forms\Components\TextInput::make('passing_score')
                            ->label('Score minimum pour réussir (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(70)
                            ->required(),

                        Forms\Components\TextInput::make('max_attempts')
                            ->label('Nombre maximum de tentatives')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('0 pour tentatives illimitées')
                            ->default(3),

                        Forms\Components\DateTimePicker::make('available_from')
                            ->label('Disponible à partir de')
                            ->default(now())
                            ->native(false)
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('available_until')
                            ->label('Disponible jusqu\'au')
                            ->native(false)
                            ->after('available_from')
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Options avancées')
                    ->schema([
                        Forms\Components\Toggle::make('randomize_questions')
                            ->label('Mélanger les questions')
                            ->inline(false)
                            ->helperText('Les questions seront présentées dans un ordre aléatoire')
                            ->default(false),

                        Forms\Components\Toggle::make('show_results_immediately')
                            ->label('Afficher les résultats immédiatement')
                            ->inline(false)
                            ->helperText('Afficher le score et les corrections dès la fin de l\'examen')
                            ->default(true),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Examen actif')
                            ->inline(false)
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('examable_type')
                    ->label('Type d\'examen')
                    ->getTitleFromRecordUsing(fn($record) => match ($record->examable_type) {
                        Formation::class => '📚 Formation',
                        Section::class => '📖 Section',
                        Chapter::class => '📄 Chapitre',
                        default => 'Autre',
                    })
                    ->collapsible(),
            ])
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    })
                    ->sortable(),

                TextColumn::make('examable_type')
                    ->label('Type')
                    ->formatStateUsing(fn($state): string => match ($state) {
                        Formation::class => 'Formation',
                        Section::class => 'Section',
                        Chapter::class => 'Chapitre',
                        default => 'Inconnu',
                    })
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        Formation::class => 'success',
                        Section::class => 'info',
                        Chapter::class => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('examable.title')
                    ->label('Élément associé')
                    ->searchable()
                    ->limit(40)
                    ->description(fn(Exam $record): ?string => $record->formation_title),

                TextColumn::make('duration_minutes')
                    ->label('Durée (min)')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('passing_score')
                    ->label('Score min. (%)')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->badge()
                    ->color(fn($state): string => match (true) {
                        $state === 0 => 'danger',
                        $state < 5 => 'warning',
                        $state >= 10 => 'success',
                        default => 'info',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('examable_type')
                    ->label('Type d\'élément')
                    ->options([
                        Formation::class => 'Formation',
                        Section::class => 'Section',
                        Chapter::class => 'Chapitre',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->trueLabel('Actif uniquement')
                    ->falseLabel('Inactif uniquement'),

                Tables\Filters\TernaryFilter::make('randomize_questions')
                    ->label('Questions mélangées'),

                Tables\Filters\TernaryFilter::make('show_results_immediately')
                    ->label('Résultats immédiats'),
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
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn(Builder $query) => $query->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn(Builder $query) => $query->update(['is_active' => false])),
                    Tables\Actions\BulkAction::make('duplicate')
                        ->label('Dupliquer')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (array $records): void {
                            foreach ($records as $record) {
                                $exam = Exam::find($record);
                                $newExam = $exam->replicate();
                                $newExam->title = "Copie de {$exam->title}";
                                $newExam->save();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExams::route('/'),
            'create' => Pages\CreateExam::route('/create'),
            'view' => Pages\ViewExam::route('/{record}/show'),
            'edit' => Pages\EditExam::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['examable'])
            ->withCount('questions');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string)self::getModel()::count();
    }
}
