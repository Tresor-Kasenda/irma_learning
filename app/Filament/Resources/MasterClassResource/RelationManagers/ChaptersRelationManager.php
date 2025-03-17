<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterClassResource\RelationManagers;

use App\Models\Chapter;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

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
                    ->slideOver()
                    ->label('Ajouter un chapitre'),
            ])
            ->actions([
                Tables\Actions\Action::make('examination')
                    ->label(fn(Chapter $record) => $record->examination()->exists() ? 'Modifier l\'évaluation' : 'Créer une évaluation')
                    ->icon(fn(Chapter $record) => $record->examination()->exists() ? 'heroicon-o-pencil' : 'heroicon-o-plus')
                    ->button()
                    ->slideOver()
                    ->form(self::getExaminationForms())
                    ->action(function (array $data, Chapter $record) {
                        if ($record->examination()->exists()) {
                            $record->examination()->update($data);
                            $message = 'évaluation modifié';
                        } else {
                            $this->getOwnerRecord()->examinations()->create([
                                ...$data,
                                'chapter_id' => $record->id,
                            ]);
                            $message = 'évaluation créé';
                        }

                        Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->slideOver()
                    ->label('Modifier'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

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
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu du cours')
                            ->fileAttachmentsDirectory('events')
                            ->columnSpanFull()
                            ->disableGrammarly(),
                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->fileAttachmentsDirectory('events')
                            ->columnSpanFull()
                            ->disableGrammarly(),
                        Toggle::make('is_final_chapter')
                            ->inline()
                            ->label('Definir comme dernier chapitre'),
                    ]),
            ]);
    }

    protected static function getExaminationForms(): array
    {
        return [
            Forms\Components\TextInput::make('title')
                ->label('Titre de l\'examen')
                ->required()
                ->default(fn(Chapter $record) => $record->examination?->title),
            Forms\Components\TextInput::make('passing_score')
                ->label('Score de réussite')
                ->required()
                ->default(fn(Chapter $record) => $record->examination?->passing_score),
            Forms\Components\TextInput::make('duration')
                ->label('Durée')
                ->required()
                ->helperText("Durée de l'examen en minutes")
                ->default(fn(Chapter $record) => $record->examination?->duration),
            Forms\Components\FileUpload::make('path')
                ->label('Fichier')
                ->required()
                ->directory('examinations')
                ->maxSize(10240)
                ->downloadable()
                ->default(fn(Chapter $record) => $record->examination?->path),
            Forms\Components\FileUpload::make('files')
                ->label('Fichiers supplémentaires')
                ->multiple()
                ->directory('examinations/additional')
                ->maxSize(10240)
                ->downloadable()
                ->default(fn(Chapter $record) => $record->examination?->files),
            Forms\Components\RichEditor::make('description')
                ->label('Description')
                ->required()
                ->default(fn(Chapter $record) => $record->examination?->description),
        ];
    }
}
