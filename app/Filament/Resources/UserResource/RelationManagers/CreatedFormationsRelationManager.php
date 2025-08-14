<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\FormationLevelEnum;
use App\Models\Formation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CreatedFormationsRelationManager extends RelationManager
{
    protected static string $relationship = 'createdFormations';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $icon = 'heroicon-o-academic-cap';

    protected static ?string $title = 'Formations créées';

    public function form(Form $form): Form
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
                            ->columnSpanFull(),


                        Forms\Components\RichEditor::make('description')
                            ->label('Description complète')
                            ->columnSpanFull()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Configuration')
                    ->schema([
                        Forms\Components\Select::make('difficulty_level')
                            ->label('Niveau de difficulté')
                            ->options(FormationLevelEnum::class)
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
                            ->relationship('creator', 'name')
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl('https://via.placeholder.com/40x40?text=F'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('difficulty_level')
                    ->label('Niveau')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        FormationLevelEnum::BEGINNER => 'success',
                        FormationLevelEnum::INTERMEDIATE => 'warning',
                        FormationLevelEnum::ADVANCED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('EUR')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Étudiants')
                    ->getStateUsing(fn(Formation $record) => $record->getEnrollmentCount())
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('modules_count')
                    ->label('Modules')
                    ->counts('modules')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Vedette')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->label('Niveau')
                    ->options(FormationLevelEnum::class)
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->trueLabel('Actives seulement')
                    ->falseLabel('Inactives seulement')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('En vedette')
                    ->boolean()
                    ->trueLabel('En vedette seulement')
                    ->falseLabel('Non mises en avant')
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Créer une formation')
                    ->icon('heroicon-o-plus')
                    ->slideOver()
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Modifier')
                        ->icon('heroicon-o-pencil')
                        ->color('info')
                        ->slideOver(),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),
                    Tables\Actions\Action::make('view_formation')
                        ->label('Voir')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        //->url(fn(Formation $record) => route('filament.admin.resources.formations.view', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('duplicate')
                        ->label('Dupliquer')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->action(function (Formation $record) {
                            $newFormation = $record->replicate();
                            $newFormation->title = $record->title . ' (Copie)';
                            $newFormation->slug = null;
                            $newFormation->save();

                            $this->mountedTableActionRecord = $newFormation->getKey();
                        })
                        ->successNotificationTitle('Formation dupliquée avec succès'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn(Collection $records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn(Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
