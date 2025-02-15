<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterClassResource\RelationManagers;

use App\Models\Chapter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Chapitre')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre du chapitre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('points')
                            ->label('Ponderation')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('path')
                            ->directory('chapters')
                            ->label('Contenue du chapitre')
                            ->downloadable()
                            ->previewable()
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // Taille maximale de 10MB
                            ->deletable()
                            ->uploadingMessage('Uploading certification...')
                            ->columnSpanFull(),
                        Forms\Components\MarkdownEditor::make('description')
                            ->label('Contenu du cours')
                            ->fileAttachmentsDirectory('events')
                            ->columnSpanFull()
                            ->disableGrammarly(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('path')
                    ->description(fn(Chapter $record): string => $record->path ?? '', position: 'above')
                    ->label('Titre du chapitre')
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre du chapitre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('points')
                    ->label('Ponderation')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-m-plus-circle')
                    ->label('Ajouter un chapitre'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
