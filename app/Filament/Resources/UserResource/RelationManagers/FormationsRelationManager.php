<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\FormationLevelEnum;
use App\Models\Formation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FormationsRelationManager extends RelationManager
{
    protected static string $relationship = 'formations';

    protected static ?string $recordTitleAttribute = 'title';

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
                    ->label('Formation')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('difficulty_level')
                    ->label('Niveau')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        FormationLevelEnum::BEGINNER => 'success',
                        FormationLevelEnum::INTERMEDIATE => 'warning',
                        FormationLevelEnum::ADVANCED => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\SelectColumn::make('pivot.status')
                    ->label('Statut')
                    ->options(EnrollmentStatusEnum::class)
                    ->selectablePlaceholder(false),

                Tables\Columns\SelectColumn::make('pivot.payment_status')
                    ->label('Paiement')
                    ->options(EnrollmentPaymentEnum::class)
                    ->selectablePlaceholder(false),

                Tables\Columns\TextColumn::make('pivot.progress_percentage')
                    ->label('Progression')
                    ->suffix('%')
                    ->alignCenter()
                    ->color(fn($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('EUR')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('pivot.enrollment_date')
                    ->label('Inscrit le')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('difficulty_level')
                    ->label('Niveau')
                    ->options(FormationLevelEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('pivot.status')
                    ->label('Statut')
                    ->options(EnrollmentStatusEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('pivot.payment_status')
                    ->label('Statut de paiement')
                    ->options(EnrollmentPaymentEnum::class)
                    ->multiple(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->slideOver()
                    ->label('S\'inscrire')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(EnrollmentStatusEnum::class)
                            ->default('active')
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->label('Statut de paiement')
                            ->options(EnrollmentPaymentEnum::class)
                            ->default('pending')
                            ->required(),
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DetachAction::make()
                        ->label('Se désinscrire')
                        ->icon('heroicon-o-minus')
                        ->color('danger'),
                    Tables\Actions\Action::make('view_formation')
                        ->label('Voir formation')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        //->url(fn($record) => route('filament.admin.resources.formations.view', $record))
                        ->openUrlInNewTab(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Se désinscrire')
                        ->icon('heroicon-o-minus')
                        ->color('danger'),
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
            ->defaultSort('pivot.enrollment_date', 'desc');
    }

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
}
