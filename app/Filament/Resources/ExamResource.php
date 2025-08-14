<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamResource\Pages;
use App\Filament\Resources\ExamResource\RelationManagers;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamResource extends Resource
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

                Forms\Components\Section::make('Association')
                    ->schema([
                        Forms\Components\Select::make('examable_type')
                            ->label('Type d\'élément')
                            ->options([
                                Formation::class => 'Formation',
                                Module::class => 'Module',
                                Section::class => 'Section',
                                Chapter::class => 'Chapitre',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('examable_id', null)),

                        Forms\Components\Select::make('examable_id')
                            ->label('Élément associé')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function (Forms\Get $get): array {
                                $type = $get('examable_type');

                                if (!$type) {
                                    return [];
                                }

                                return match ($type) {
                                    Formation::class => Formation::pluck('title', 'id')->toArray(),
                                    Module::class => Module::with('formation')->get()
                                        ->mapWithKeys(fn($module) => [$module->id => "{$module->formation->title} > {$module->title}"])
                                        ->toArray(),
                                    Section::class => Section::with('module.formation')->get()
                                        ->mapWithKeys(fn($section) => [$section->id => "{$section->module->formation->title} > {$section->module->title} > {$section->title}"])
                                        ->toArray(),
                                    Chapter::class => Chapter::with('section.module.formation')->get()
                                        ->mapWithKeys(fn($chapter) => [$chapter->id => "{$chapter->section->module->formation->title} > {$chapter->section->module->title} > {$chapter->section->title} > {$chapter->title}"])
                                        ->toArray(),
                                    default => [],
                                };
                            }),
                    ])
                    ->columns(2),

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
                            ->minValue(1)
                            ->default(3),

                        Forms\Components\DateTimePicker::make('available_from')
                            ->label('Disponible à partir de')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('available_until')
                            ->label('Disponible jusqu\'au')
                            ->native(false)
                            ->default(now())
                            ->placeholder('Aucune date limite')
                            ->after('available_from'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Options avancées')
                    ->schema([
                        Forms\Components\Toggle::make('randomize_questions')
                            ->label('Mélanger les questions')
                            ->inline(false)
                            ->helperText('Les questions seront présentées dans un ordre aléatoire'),

                        Forms\Components\Toggle::make('show_results_immediately')
                            ->label('Afficher les résultats immédiatement')
                            ->inline(false)
                            ->helperText('Afficher le score et les corrections dès la fin de l\'examen'),

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
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('examable_type')
                    ->label('Type')
                    ->formatStateUsing(fn($state): string => match ($state) {
                        Formation::class => 'Formation',
                        Module::class => 'Module',
                        Section::class => 'Section',
                        Chapter::class => 'Chapitre',
                        default => 'Inconnu',
                    })
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        Formation::class => 'success',
                        Module::class => 'info',
                        Section::class => 'warning',
                        Chapter::class => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('examable.title')
                    ->label('Élément associé')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durée (min)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('passing_score')
                    ->label('Score min. (%)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Questions')
                    ->counts('questions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('attempts_count')
                    ->label('Tentatives')
                    ->counts('attempts')
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
                        Module::class => 'Module',
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
                ])
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
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
            RelationManagers\AttemptsRelationManager::class,
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
            ->with(['examable']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
