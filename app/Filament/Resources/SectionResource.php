<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Filament\Resources\SectionResource\RelationManagers;
use App\Models\Module;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'Sections';

    protected static ?string $navigationGroup = 'Gestion des formations';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations de la section')
                    ->schema([
                        Forms\Components\Select::make('module_id')
                            ->label('Module')
                            ->relationship('module', 'title')
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn($record) => strlen($record->title) > 50
                                ? substr($record->title, 0, 50) . '...'
                                : $record->title
                            )
                            ->extraAttributes(function ($get) {
                                $formationId = $get('module_id_id');
                                if ($formationId) {
                                    $formation = Module::find($formationId);
                                    return $formation ? ['title' => $formation->title] : [];
                                }
                                return [];
                            })
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

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('order_position')
                            ->label('Position dans le module')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->minValue(1),

                        Forms\Components\TextInput::make('estimated_duration')
                            ->label('Durée estimée (minutes)')
                            ->numeric()
                            ->minValue(0)
                            ->default(30),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Section active')
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
                Tables\Columns\TextColumn::make('module.formation.title')
                    ->label('Formation')
                    ->sortable()
                    ->searchable()
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('module.title')
                    ->label('Module')
                    ->sortable()
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre de la section')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_position')
                    ->label('Position')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_duration')
                    ->label('Durée (min)')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('chapters_count')
                    ->label('Chapitres')
                    ->counts('chapters')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('module')
                    ->label('Module')
                    ->relationship('module', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->trueLabel('Active uniquement')
                    ->falseLabel('Inactive uniquement'),
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation(),
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
            ->with(['module.formation']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
