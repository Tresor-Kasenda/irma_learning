<?php

namespace App\Filament\Resources;

use App\Enums\ExamAttemptEnum;
use App\Filament\Resources\ExamAttemptResource\Pages;
use App\Filament\Resources\ExamAttemptResource\RelationManagers;
use App\Models\ExamAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamAttemptResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Tentatives d\'examen';

    protected static ?string $navigationGroup = 'Évaluations';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la tentative')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Étudiant')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('exam_id')
                            ->label('Examen')
                            ->relationship('exam', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('attempt_number')
                            ->label('Numéro de tentative')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(ExamAttemptEnum::class)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Résultats')
                    ->schema([
                        Forms\Components\TextInput::make('score')
                            ->label('Score obtenu')
                            ->numeric()
                            ->minValue(0),

                        Forms\Components\TextInput::make('max_score')
                            ->label('Score maximum')
                            ->numeric()
                            ->minValue(1),

                        Forms\Components\TextInput::make('percentage')
                            ->label('Pourcentage (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01),

                        Forms\Components\TextInput::make('time_taken')
                            ->label('Temps pris (secondes)')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Dates importantes')
                    ->schema([
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('Commencé le')
                            ->native(false),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Terminé le')
                            ->native(false)
                            ->after('started_at'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Réponses (JSON)')
                    ->schema([
                        Forms\Components\Textarea::make('answers')
                            ->label('Réponses stockées')
                            ->helperText('Format JSON des réponses de l\'étudiant')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Étudiant')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Examen')
                    ->sortable()
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('attempt_number')
                    ->label('Tentative #')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'abandoned' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('percentage')
                    ->label('Score (%)')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->color(function ($state, $record): string {
                        if (!$state || !$record->exam) return 'gray';
                        return $state >= $record->exam->passing_score ? 'success' : 'danger';
                    }),

                Tables\Columns\TextColumn::make('score')
                    ->label('Points')
                    ->formatStateUsing(fn($record): string => $record->score && $record->max_score ? "{$record->score}/{$record->max_score}" : 'N/A'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('time_taken')
                    ->label('Durée')
                    ->formatStateUsing(fn($state): string => $state ? gmdate('H:i:s', $state) : 'N/A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Commencé le')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Terminé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->label('Étudiant')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('exam')
                    ->label('Examen')
                    ->relationship('exam', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ExamAttemptEnum::class),

                Tables\Filters\Filter::make('passed')
                    ->label('Réussi')
                    ->query(fn(Builder $query): Builder => $query->passed()),

                Tables\Filters\Filter::make('failed')
                    ->label('Échoué')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'completed')
                        ->whereColumn('percentage', '<', 'exams.passing_score')
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Marquer comme terminé')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(ExamAttempt $record) => $record->complete())
                    ->visible(fn(ExamAttempt $record): bool => $record->status === 'in_progress'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UserAnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamAttempts::route('/'),
            'create' => Pages\CreateExamAttempt::route('/create'),
            'view' => Pages\ViewExamAttempt::route('/{record}'),
            'edit' => Pages\EditExamAttempt::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['user', 'exam']);
    }
}
