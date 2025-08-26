<?php

namespace App\Filament\Resources;

use App\Enums\FormationLevelEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\FormationResource\Pages;
use App\Filament\Resources\FormationResource\RelationManagers;
use App\Models\Formation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Str;

class FormationResource extends Resource
{
    protected static ?string $model = Formation::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Formations';

    protected static ?string $navigationGroup = 'Gestion des formations';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->helperText('Le titre principal qui apparaîtra aux étudiants')
                            ->afterStateUpdated(
                                fn(string $context, $state, Forms\Set $set) => $context === 'create' ? $set('slug', Str::slug($state)) : null
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->readOnly()
                            ->maxLength(255)
                            ->rules(['alpha_dash'])
                            ->helperText('URL conviviale (générée automatiquement)')
                            ->unique(Formation::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('short_description')
                            ->label('Description courte')
                            ->autosize()
                            ->maxLength(255)
                            ->placeholder('Une brève description de la formation')
                            ->rows(4)
                            ->columnSpanFull(),


                        Forms\Components\RichEditor::make('description')
                            ->label('Description complète')
                            ->placeholder('Une description détaillée de la formation')
                            ->columnSpanFull()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Select::make('difficulty_level')
                            ->label('Niveau de difficulté')
                            ->options(collect(FormationLevelEnum::cases())->mapWithKeys(
                                fn(FormationLevelEnum $enum) => [$enum->value => $enum->getLabel()]
                            ))
                            ->required(),

                        Forms\Components\TextInput::make('price')
                            ->label('Prix')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),

                        Forms\Components\TextInput::make('certification_threshold')
                            ->label('Seuil de certification (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(80),

                        Forms\Components\Select::make('created_by')
                            ->label('Créé par')
                            ->relationship('creator', 'name', fn($query) => $query->whereIn('role', [UserRoleEnum::ADMIN, UserRoleEnum::INSTRUCTOR]))
                            ->searchable()
                            ->preload()
                            ->default(auth()->id())
                            ->required(),

                        Forms\Components\TextInput::make('duration_hours')
                            ->label('Durée (heures)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        Forms\Components\Select::make('language')
                            ->label('Langue')
                            ->options([
                                'fr' => 'Français',
                                'en' => 'Anglais',
                                'es' => 'Espagnol',
                                'de' => 'Allemand',
                                'it' => 'Italien',
                                'pt' => 'Portugais',
                                'ru' => 'Russe',
                            ])
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Statuts et options')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->inline(false)
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->inline(false)
                            ->label('En vedette'),

                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->separator(','),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Image')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->directory('formations')
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth(1200)
                            ->imageResizeTargetHeight(630)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->size(40),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
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

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Créateur')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('difficulty_level')
                    ->label('Niveau')
                    ->badge()
                    ->color(fn(FormationLevelEnum $state): string => match ($state) {
                        FormationLevelEnum::BEGINNER => 'success',
                        FormationLevelEnum::INTERMEDIATE => 'warning',
                        FormationLevelEnum::ADVANCED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->trueLabel('Actif uniquement')
                    ->falseLabel('Inactif uniquement')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('En vedette'),

                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->label('Niveau de difficulté')
                    ->options(FormationLevelEnum::class),

                Tables\Filters\SelectFilter::make('creator')
                    ->label('Créateur')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ModulesRelationManager::class,
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormations::route('/'),
            'create' => Pages\CreateFormation::route('/create'),
            'view' => Pages\ViewFormation::route('/{record}/show'),
            'edit' => Pages\EditFormation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
