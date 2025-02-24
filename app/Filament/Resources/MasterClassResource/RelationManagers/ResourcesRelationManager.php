<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterClassResource\RelationManagers;

use App\Enums\MasterClassResourceEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final class ResourcesRelationManager extends RelationManager
{
    protected static string $relationship = 'resources';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Chapitre')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre du resources')
                            ->placeholder('Titre du chapitre')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->label('Type de ressource')
                            ->required()
                            ->searchable()
                            ->placeholder('Sélectionner le type de ressource')
                            ->options([
                                MasterClassResourceEnum::PDF->value => 'PDF',
                                MasterClassResourceEnum::VIDEO->value => 'Vidéo',
                                MasterClassResourceEnum::LINK->value => 'Lien',
                            ])
                            ->reactive(),
                        Forms\Components\TextInput::make('file_path')
                            ->label('Lien')
                            ->url()
                            ->required()
                            ->placeholder('Lien de la ressource')
                            ->columnSpanFull()
                            ->visible(fn($get) => $get('type') === 'link'),
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Fichier')
                            ->directory('resources')
                            ->downloadable()
                            ->previewable()
                            ->placeholder('Fichier de la ressource')
                            ->acceptedFileTypes(fn($get) => match ($get('type')) {
                                'pdf' => ['application/pdf'],
                                'video' => ['video/*'],
                                default => [],
                            })
                            ->maxSize(10240)
                            ->required(fn($get) => in_array($get('type'), ['pdf', 'video']))
                            ->visible(fn($get) => in_array($get('type'), ['pdf', 'video']))
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu')
                            ->placeholder('Contenu de la ressource')
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
                Tables\Columns\TextColumn::make('file_path')
                    ->label('Fichier')
                    ->searchable()
                    ->url(fn($record) => $record->type === 'link' ? $record->file_path : null)
                    ->badge()
                    ->extraAttributes(fn($record) => $record->type !== 'link' ? ['data-filament-download-url' => $record->file_path] : [])
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre du chapitre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type de fichier')
                    ->searchable()
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajouter une ressource')
                    ->slideOver()
                    ->icon('heroicon-m-plus-circle'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
