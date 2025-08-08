<?php

namespace App\Filament\Resources;

use App\Enums\TrainingStatusEnum;
use App\Filament\Resources\TrainingResource\Pages;
use App\Filament\Resources\TrainingResource\RelationManagers;
use App\Models\Training;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class TrainingResource extends Resource
{
    protected static ?string $model = Training::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Gestion de formation';

    protected static ?string $label = 'Formations';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Informations du cours')
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre du cours')
                                    ->placeholder('Titre du cours')
                                    ->required()
                                    ->unique(Training::class, 'title', ignoreRecord: true)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('slug', str($state)->slug());
                                    })
                                    ->live()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->label('Slug du cours')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->placeholder('Slug du cours')
                                    ->maxLength(255),
                                Textarea::make('content')
                                    ->label('Introduction')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->placeholder('Introduction du cours')
                                    ->autosize()
                                    ->required()
                                    ->disableGrammarly(),
                                RichEditor::make('description')
                                    ->label('Description du cours')
                                    ->fileAttachmentsDirectory('events')
                                    ->columnSpanFull()
                                    ->required()
                                    ->disableGrammarly(),
                                ToggleButtons::make('status')
                                    ->options([
                                        TrainingStatusEnum::PUBLISHED->value => 'Publié',
                                        TrainingStatusEnum::UNPUBLISHED->value => 'Non publié',
                                        TrainingStatusEnum::DRAFT->value => 'Brouillon',
                                        TrainingStatusEnum::ARCHIVED->value => 'Archivé',
                                    ])
                                    ->colors([
                                        TrainingStatusEnum::PUBLISHED->color(),
                                        TrainingStatusEnum::UNPUBLISHED->color(),
                                        TrainingStatusEnum::DRAFT->color(),
                                        TrainingStatusEnum::ARCHIVED->color(),
                                    ])
                                    ->inline()
                                    ->required()
                                    ->columnSpanFull()
                                    ->reactive()
                            ]),
                    ])->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Photo couverture')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Photo de couverture')
                                    ->image()
                                    ->directory('trainings')
                                    ->required()
                                    ->columnSpanFull()
                                    ->circleCropper()
                                    ->nullable(),
                            ]),
                        Section::make('Information du prix')
                            ->schema([
                                TextInput::make('duration')
                                    ->numeric()
                                    ->columnSpan(['lg' => 1])
                                    ->placeholder('Durée du cours')
                                    ->label('Durée du cours'),
                                TextInput::make('price')
                                    ->numeric()
                                    ->placeholder('Prix du cours')
                                    ->label('Prix du cours'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Couverture')
                    ->circular()
                    ->height(100),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->icon('heroicon-o-currency-dollar')
                    ->prefix('$')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn($state): string => match ($state) {
                        TrainingStatusEnum::PUBLISHED->value => 'success',
                        TrainingStatusEnum::UNPUBLISHED->value => 'warning',
                        TrainingStatusEnum::DRAFT->value => 'info',
                        TrainingStatusEnum::ARCHIVED->value => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        TrainingStatusEnum::PUBLISHED->value => TrainingStatusEnum::PUBLISHED->label(),
                        TrainingStatusEnum::UNPUBLISHED->value => TrainingStatusEnum::UNPUBLISHED->label(),
                        TrainingStatusEnum::DRAFT->value => TrainingStatusEnum::DRAFT->label(),
                        TrainingStatusEnum::ARCHIVED->value => TrainingStatusEnum::ARCHIVED->label(),
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->color('success'),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil-square')
                        ->color('primary'),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ])
                    ->label('Details')
                    ->tooltip('Detail')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(ActionSize::Small)
                    ->color('info')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Créer une formation')
                    ->icon('heroicon-o-plus')
                    ->color('success'),
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
            'index' => Pages\ListTrainings::route('/'),
            'create' => Pages\CreateTraining::route('/create'),
            'edit' => Pages\EditTraining::route('/{record}/edit'),
            'view' => Pages\ViewTraining::route('/{record}/view'),
        ];
    }
}
