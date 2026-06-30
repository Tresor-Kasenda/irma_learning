<?php

declare(strict_types=1);

namespace App\Filament\Resources\SectionResource\RelationManagers;

use App\Filament\Resources\ExamResource;
use App\Models\Exam;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class ExamRelationManager extends RelationManager
{
    protected static string $relationship = 'exam';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Examen de la section';

    protected static BackedEnum|string|null $icon = 'heroicon-o-clipboard-document-check';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titre de l\'examen')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('passing_score')
                    ->label('Score minimum (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(70)
                    ->required(),

                Forms\Components\TextInput::make('duration_minutes')
                    ->label('Durée (min)')
                    ->numeric()
                    ->minValue(1)
                    ->default(30)
                    ->required(),

                Forms\Components\TextInput::make('max_attempts')
                    ->label('Tentatives max')
                    ->numeric()
                    ->minValue(0)
                    ->default(3)
                    ->helperText('0 = illimité'),

                Forms\Components\Toggle::make('randomize_questions')
                    ->label('Mélanger les questions')
                    ->default(false),

                Forms\Components\Toggle::make('show_results_immediately')
                    ->label('Résultats immédiats')
                    ->default(true),

                Forms\Components\Toggle::make('is_active')
                    ->label('Examen actif')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->limit(40),

                Tables\Columns\TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Questions')
                    ->badge()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('passing_score')
                    ->label('Seuil')
                    ->suffix(' %')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Créer l\'examen de la section')
                    ->icon('heroicon-o-plus-circle')
                    ->slideOver()
                    ->visible(fn (): bool => ! $this->getOwnerRecord()->exam()->exists()),
            ])
            ->actions([
                Actions\Action::make('questions')
                    ->label('Gérer les questions')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn (Exam $record): string => ExamResource::getUrl('edit', ['record' => $record])),

                Actions\EditAction::make()
                    ->label('Modifier')
                    ->slideOver(),

                Actions\DeleteAction::make()
                    ->label('Supprimer')
                    ->requiresConfirmation(),
            ]);
    }
}
