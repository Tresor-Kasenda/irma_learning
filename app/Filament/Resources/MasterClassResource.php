<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\MasterClassEnum;
use App\Filament\Resources\MasterClassResource\Pages;
use App\Filament\Resources\MasterClassResource\RelationManagers;
use App\Models\MasterClass;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class MasterClassResource extends Resource
{
    protected static ?string $model = MasterClass::class;

    protected static ?string $navigationGroup = 'Gestion de formation';

    protected static ?string $navigationIcon = 'heroicon-o-book-open'; // Icône du menu (facultatif)

    protected static ?string $label = 'Cours'; // Nom de la ressource

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du cours')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre du cours')
                            ->disabled(),
                        TextInput::make('sub_title')
                            ->label('Sous titre du cours')
                            ->required()
                            ->placeholder('Sous-titre de l\'événement')
                            ->maxLength(255),
                        TextInput::make('duration')
                            ->numeric()
                            ->columnSpan(['lg' => 1])
                            ->placeholder('Durée du cours')
                            ->label('Durée du cours'),
                        TextInput::make('price')
                            ->numeric()
                            ->placeholder('Prix du cours')
                            ->label('Prix du cours'),
                        Select::make('status')
                            ->placeholder('Statut')
                            ->label('Statut')
                            ->reactive()
                            ->searchable()
                            ->options([
                                MasterClassEnum::UNPUBLISHED->value => 'Non publié',
                                MasterClassEnum::PUBLISHED->value => 'Publié',
                            ])
                            ->required(),
                        FileUpload::make('path')
                            ->label('Photo de couverture')
                            ->image()
                            ->required()
                            ->circleCropper()
                            ->nullable(),
                        RichEditor::make('presentation')
                            ->label('Presentation')
                            ->fileAttachmentsDirectory('events')
                            ->columnSpanFull()
                            ->required()
                            ->disableGrammarly(),

                        RichEditor::make('description')
                            ->label('Description du cours')
                            ->fileAttachmentsDirectory('events')
                            ->columnSpanFull()
                            ->required()
                            ->disableGrammarly(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Événement')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Durée')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        MasterClassEnum::PUBLISHED->value => 'success',
                        MasterClassEnum::UNPUBLISHED->value => 'danger',
                    })
                    ->searchable()
                    ->sortable(),
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
            RelationManagers\ChaptersRelationManager::class,
            RelationManagers\ResourcesRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = self::$model;

        return (string)$modelClass::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterClasses::route('/'),
            'edit' => Pages\EditMasterClass::route('/{record}/edit'),
        ];
    }
}
