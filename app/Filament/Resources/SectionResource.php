<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Filament\Resources\SectionResource\RelationManagers;
use App\Models\Section;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Sections';

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_position')
                    ->label('Position')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('formation.title')
                    ->label('Formation')
                    ->sortable()
                    ->searchable()
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    })
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Titre de la section')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    })
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Durée (min)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('formation')
                    ->label('Formation')
                    ->relationship('formation', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->trueLabel('Active uniquement')
                    ->falseLabel('Inactive uniquement'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Voir')
                        ->icon('heroicon-o-eye'),
                    EditAction::make()
                        ->label('Modifier')
                        ->icon('heroicon-o-pencil'),
                    DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                    BulkAction::make('toggle_active')
                        ->label('Activer/Désactiver')
                        ->icon('heroicon-o-power')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => ! $record->is_active]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('order_position', 'asc')
            ->reorderable('order_position');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('Informations de la section')
                    ->schema([
                        Forms\Components\Select::make('formation_id')
                            ->label('Formation')
                            ->relationship('formation', 'title')
                            ->getOptionLabelFromRecordUsing(fn ($record) => mb_strlen($record->title) > 50
                                ? mb_substr($record->title, 0, 50).'...'
                                : $record->title
                            )
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Titre de la section')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('description')
                            ->label('Contenu de la section')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('duration')
                            ->label('Durée estimée (minutes)')
                            ->numeric()
                            ->helperText('Calculée automatiquement si la formation a une durée définie.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Section active')
                            ->columnSpanFull()
                            ->inline(false)
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChaptersRelationManager::class,
            RelationManagers\ExamRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'view' => Pages\ViewSection::route('/{record}'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with('formation');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }
}
