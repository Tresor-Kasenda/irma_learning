<?php

namespace App\Filament\Resources\MasterClassResource\RelationManagers;

use App\Models\FinalExamination;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class FinalExamRelationManager extends RelationManager
{
    protected static string $relationship = 'finalExam';

    protected static ?string $title = "Examen finale";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Examen general')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre du resources')
                            ->placeholder('Titre du chapitre')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('deadline')
                            ->placeholder('Date de fin de l\'examen')
                            ->required()
                            ->native(false)
                            ->label('Date de fin du cours'),
                        Forms\Components\FileUpload::make('path')
                            ->label('Fichier')
                            ->directory('resources')
                            ->downloadable()
                            ->previewable()
                            ->placeholder('Fichier de l\'examen')
                            ->maxSize(10240)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('files')
                            ->label('Fichier')
                            ->directory('resources')
                            ->downloadable()
                            ->previewable()
                            ->multiple()
                            ->placeholder('Fichier de l\'examen')
                            ->maxSize(10240)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('description')
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
                Tables\Columns\TextColumn::make('title')
                    ->label("titre"),
                Tables\Columns\TextColumn::make('deadline')
                    ->label('date de fin de soumission'),
                Tables\Columns\TextColumn::make('path')
                    ->label('Fichier')
                    ->url(fn(FinalExamination $record): ?string => $record->path ? Storage::url($record->path) : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->visible(fn(): bool => !$this->getOwnerRecord()->finalExam()->exists()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
