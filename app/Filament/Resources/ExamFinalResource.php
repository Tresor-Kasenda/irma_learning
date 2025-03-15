<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamFinalResource\Pages;
use App\Filament\Resources\ExamFinalResource\RelationManagers;
use App\Models\ExamFinal;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ExamFinalResource extends Resource
{
    protected static ?string $model = ExamFinal::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Gestion de formation';

    protected static ?string $label = 'Examen final'; // Nom de la ressource

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Étudiant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('masterClass.title')
                    ->label('Étudiant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Étudiant')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_path')
                    ->label('Fichier')
                    ->url(fn(ExamFinal $record): ?string => $record->file_path ? Storage::url($record->file_path) : null)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExamFinals::route('/'),
            'create' => Pages\CreateExamFinal::route('/create'),
            'edit' => Pages\EditExamFinal::route('/{record}/edit'),
        ];
    }
}
