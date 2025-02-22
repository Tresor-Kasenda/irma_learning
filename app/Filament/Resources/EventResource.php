<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\Widgets\EventStats;
use App\Models\Event;
use App\Models\EventType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

final class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationGroup = 'Gestions des events'; // Groupe de menu

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; // Icône du menu (facultatif)

    protected static ?string $label = 'Liste des events'; // Nom de la ressource

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    Section::make("Informations sur l'événement")
                        ->schema([
                            Select::make('event_type_id')
                                ->label("Type d'événement")
                                ->reactive()
                                ->searchable()
                                ->options(EventType::pluck('title', 'id')->toArray())
                                ->createOptionForm([
                                    TextInput::make('title')
                                        ->label("Nom de l'evenement")
                                        ->required()
                                        ->maxLength(255),
                                    FileUpload::make('image')
                                        ->label("Image de l'evenement")
                                        ->image()
                                        ->imageEditor()
                                        ->nullable(),
                                    Textarea::make('description')
                                        ->autosize()
                                        ->nullable(),
                                ])
                                ->columnSpan(['lg' => 1])
                                ->required(),
                            TextInput::make('title')
                                ->label("Titre de l'événement")
                                ->required()
                                ->columnSpan(['lg' => 1])
                                ->unique('events', 'title', ignoreRecord: true)
                                ->maxLength(255),
                            TextInput::make('town')
                                ->label('Ville')
                                ->required()
                                ->columnSpan(['lg' => 1])
                                ->maxLength(255),
                            TextInput::make('address')
                                ->label('Adresse')
                                ->required()
                                ->columnSpan(['lg' => 1])
                                ->maxLength(255),
                        ])->columns(2),
                    Section::make('Tarification')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            TextInput::make('tarif_membre')
                                ->label('Tarif Membre')
                                ->numeric()
                                ->columnSpan(['lg' => 1])
                                ->required()
                                ->nullable(),
                            TextInput::make('tarif_non_membre')
                                ->label('Tarif Non Membre')
                                ->numeric()
                                ->required()
                                ->columnSpan(['lg' => 1])
                                ->nullable(),
                        ])->columns(2),
                    Section::make('Description')
                        ->icon('heroicon-o-document-duplicate')
                        ->schema([
                            RichEditor::make('description')
                                ->label('Introduction')
                                ->fileAttachmentsDirectory('events')
                                ->disableGrammarly()
                                ->required(),
                            RichEditor::make('content')
                                ->label('Contenue de l\'evenement')
                                ->fileAttachmentsDirectory('events')
                                ->disableGrammarly(),
                            Toggle::make('status')
                                ->label('Activer'),
                        ]),
                ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Image Evenement')
                            ->icon('heroicon-o-camera')
                            ->schema([
                                FileUpload::make('image')
                                    ->label('Image')
                                    ->image()
                                    ->required()
                                    ->circleCropper()
                                    ->nullable(),
                            ]),

                        Section::make('Heure et Date')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                DatePicker::make('date')
                                    ->label('Jour de l\'evenement')
                                    ->native(false)
                                    ->displayFormat('d F Y')
                                    ->columnSpanFull()
                                    ->placeholder('Choisir une date')
                                    ->minDate(fn ($operation) => $operation === 'create' ? now() : null)
                                    ->required(),
                                TimePicker::make('heure_debut')
                                    ->prefix('Start')
                                    ->label('Heure de debut')
                                    ->placeholder('Choisir une heure')
                                    ->live()
                                    ->datalist([
                                        '08:00',
                                        '09:00',
                                        '10:00',
                                        '11:00',
                                        '12:00',
                                        '13:00',
                                        '14:00',
                                        '15:00',
                                        '16:00',
                                        '17:00',
                                        '18:00',
                                        '19:00',
                                        '20:00',
                                        '21:00',
                                        '22:00',
                                    ])
                                    ->nullable(),
                                TimePicker::make('heure_fin')
                                    ->prefix('End')
                                    ->label('Heure de fin')
                                    ->placeholder('Choisir une heure')
                                    ->live()
                                    ->datalist([
                                        '08:00',
                                        '09:00',
                                        '10:00',
                                        '11:00',
                                        '12:00',
                                        '13:00',
                                        '14:00',
                                        '15:00',
                                        '16:00',
                                        '17:00',
                                        '18:00',
                                        '19:00',
                                        '20:00',
                                        '21:00',
                                        '22:00',
                                    ])
                                    ->nullable()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $heureDebut = Carbon::parse($get('heure_debut'));
                                        $heureFin = Carbon::parse($state);
                                        if ($heureDebut && $heureFin) {
                                            $duree = $heureFin->diffInHours($heureDebut);
                                            $set('duration', abs($duree));
                                        }
                                    }),
                            ]),
                        Section::make("Durée de l'evenement")
                            ->icon('heroicon-o-clock')
                            ->iconColor('blue')
                            ->schema([
                                TextInput::make('duration')
                                    ->required()
                                    ->readOnly()
                                    ->numeric(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Image')
                    ->toggleable(),
                TextColumn::make('type.title')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('status')
                    ->label('Statut')
                    ->boolean(),
                TextColumn::make('date')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('town')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('event_type_id')
                    ->name('event_type_id')
                    ->label('Type d\'événement')
                    ->options(EventType::pluck('title', 'id')->toArray())
                    ->multiple()
                    ->searchable(),
            ])
            ->actions([
                ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->groups([
                Tables\Grouping\Group::make('date')
                    ->label("Date de l'événement")
                    ->getTitleFromRecordUsing(function ($record) {
                        return Carbon::parse(data_get($record, 'date'))->format(Table::$defaultDateDisplayFormat);
                    })
                    ->collapsible(),
                Tables\Grouping\Group::make('type_event_id')
                    ->label("Type d'événement")
                    ->getTitleFromRecordUsing(function ($record) {
                        return data_get($record, 'typeEvent.name');
                    })
                    ->collapsible(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ])
            ->emptyStateIcon('heroicon-m-bookmark')
            ->emptyStateHeading('Aucun evenement enregistrer')
            ->emptyStateDescription('Une fois que vous ajouter un evenement, il apparaîtra ici')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('heroicon-m-plus-circle')
                    ->label('Ajouter un evenement'),
            ]);
    }

    public static function getLabel(): ?string
    {
        return 'Evenements';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            EventStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'view' => Pages\ViewEvents::route('/{record}/view'),
        ];
    }
}
