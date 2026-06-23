<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Filament\Resources\SectionResource\RelationManagers;
use App\Models\Formation;
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
use Filament\Schemas\Components\Utilities\Get;
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

    protected static string|UnitEnum|null $navigationGroup = 'Gestion des formations';

    protected static ?int $navigationSort = 3;

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
                                $record->update(['is_active' => !$record->is_active]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('order_position', 'asc');
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
                            ->getOptionLabelFromRecordUsing(fn($record) => mb_strlen($record->title) > 50
                                ? mb_substr($record->title, 0, 50) . '...'
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
                        Forms\Components\TextInput::make('order_position')
                            ->label('Position dans la formation')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->default(function (Get $get) {
                                $formationId = $get('formation_id');
                                if ($formationId) {
                                    return (Section::query()->whereBelongsTo($formationId)->max('order_position') ?? 0) + 1;
                                }
                                return 1;
                            })
                            ->helperText('Position automatique (prochaine disponible dans cette formation)'),

                        Forms\Components\TextInput::make('duration')
                            ->label('Durée estimée (minutes)')
                            ->numeric()
                            ->default(function (Get $get) {
                                $formationId = $get('formation_id');
                                if (!$formationId) {
                                    return null;
                                }

                                $formation = Formation::find($formationId);
                                if (!$formation || !$formation->duration_hours) {
                                    return null;
                                }

                                // Convert formation duration from hours to minutes
                                $formationDurationMinutes = $formation->duration_hours * 60;

                                // Get the number of existing sections + 1 (for the new section being added)
                                $totalSections = Section::where('formation_id', $formationId)->count() + 1;

                                // Calculate average duration per section
                                return (int)round($formationDurationMinutes / $totalSections);
                            })
                            ->helperText('Durée calculée automatiquement. Vous pouvez la modifier si nécessaire.'),

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
        return (string)self::getModel()::count();
    }
}
