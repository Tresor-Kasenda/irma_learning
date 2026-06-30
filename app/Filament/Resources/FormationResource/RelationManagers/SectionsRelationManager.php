<?php

declare(strict_types=1);

namespace App\Filament\Resources\FormationResource\RelationManagers;

use App\Filament\Resources\SectionResource;
use App\Models\Section;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Sections';

    protected static BackedEnum|string|null $icon = 'heroicon-o-folder-open';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titre de la section')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('description')
                    ->label('Description')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('order_position')
                    ->label('Position')
                    ->numeric()
                    ->default(fn (): int => ((int) $this->getOwnerRecord()->sections()->max('order_position')) + 1)
                    ->required(),

                Forms\Components\TextInput::make('duration')
                    ->label('Durée estimée (min)')
                    ->numeric()
                    ->minValue(0),

                Forms\Components\Toggle::make('is_active')
                    ->label('Section active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('order_position')
            ->reorderable('order_position')
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('order_position')
                    ->label('Ordre')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(45),

                Tables\Columns\TextColumn::make('chapters_count')
                    ->counts('chapters')
                    ->label('Chapitres')
                    ->badge()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('has_exam')
                    ->label('Examen')
                    ->alignCenter()
                    ->boolean()
                    ->state(fn (Section $record): bool => $record->exam()->exists()),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Ajouter une section')
                    ->icon('heroicon-o-plus-circle')
                    ->slideOver(),
            ])
            ->actions([
                Actions\Action::make('manage')
                    ->label('Chapitres & examen')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn (Section $record): string => SectionResource::getUrl('edit', ['record' => $record])),

                Actions\EditAction::make()
                    ->label('Modifier')
                    ->slideOver(),

                Actions\DeleteAction::make()
                    ->label('Supprimer')
                    ->requiresConfirmation(),
            ]);
    }
}
