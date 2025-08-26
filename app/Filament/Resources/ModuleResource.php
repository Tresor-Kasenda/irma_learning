<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleResource\Pages;
use App\Filament\Resources\ModuleResource\RelationManagers;
use App\Models\Formation;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Modules';

    protected static ?string $navigationGroup = 'Gestion des formations';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du module')
                    ->schema([
                        Forms\Components\Select::make('formation_id')
                            ->label('Formation')
                            ->relationship('formation', 'title')
                            ->getOptionLabelFromRecordUsing(fn($record) => strlen($record->title) > 50
                                ? substr($record->title, 0, 50) . '...'
                                : $record->title
                            )
                            ->extraAttributes(function ($get) {
                                $formationId = $get('formation_id');
                                if ($formationId) {
                                    $formation = Formation::find($formationId);
                                    return $formation ? ['title' => $formation->title] : [];
                                }
                                return [];
                            })
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Titre du module')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\RichEditor::make('description')
                            ->label('Contenu du module')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('order_position')
                            ->label('Position dans la formation')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->minValue(1),

                        Forms\Components\TextInput::make('estimated_duration')
                            ->label('Durée estimée (minutes)')
                            ->numeric()
                            ->minValue(0)
                            ->default(60),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Module actif')
                            ->inline(false)
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('formation.title')
                    ->label('Formation')
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre du module')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_duration')
                    ->label('Durée (min)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sections_count')
                    ->label('Sections')
                    ->counts('sections')
                    ->sortable(),

                Tables\Columns\TextColumn::make('chapters_count')
                    ->label('Chapitres')
                    ->getStateUsing(fn(Module $record): int => $record->getChaptersCount()),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
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
                    ->trueLabel('Actif uniquement')
                    ->falseLabel('Inactif uniquement'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Voir')
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->label('Modifier')
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggle_active')
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'view' => Pages\ViewModule::route('/{record}'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['formation']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
