<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationGroup = 'Gestion des inscriptions';

    protected static ?string $navigationIcon = 'heroicon-o-users'; // Icône du menu (facultatif)

    protected static ?string $label = 'Inscription'; // Nom de la ressource

    protected static ?int $navigationSort = 1;

    public static function getLabel(): ?string
    {
        return 'Tous les participants';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    Forms\Components\Section::make('Informations de l\'entreprise')
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('company')
                                ->label('Nom de la Société/Entreprise/Organisation')
                                ->string()
                                ->placeholder('Nom de la Société/Entreprise/Organisation')
                                ->required(),
                            Forms\Components\TextInput::make('sector')
                                ->label("Secteur d'activiter")
                                ->placeholder("Secteur d'activiter")
                                ->required(),
                            Forms\Components\TextInput::make('office_phone')
                                ->label('Telephone fixe')
                                ->placeholder('Telephone fixe')
                                ->tel()
                                ->required(),
                            Forms\Components\TextInput::make('town')
                                ->label('Ville')
                                ->placeholder("Ville de l'entreprise")
                                ->required(),
                        ]),
                    Forms\Components\Section::make("Information d'utilisateur")
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required(),
                            Forms\Components\TextInput::make('firstname')
                                ->required(),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required()
                                ->unique(Booking::class, 'email', ignoreRecord: true),
                            Forms\Components\TextInput::make('phone_number')
                                ->tel()
                                ->unique(Booking::class, 'phone_number', ignoreRecord: true)
                                ->required(),
                            Forms\Components\Toggle::make('status')
                                ->required(),
                        ]),
                ])
                    ->columnSpan(['lg' => 2]),
                Group::make([
                    Forms\Components\Section::make('Evenement')
                        ->columns(2)
                        ->schema([
                            Forms\Components\Select::make('event_id')
                                ->reactive()
                                ->searchable()
                                ->label("Nom de l'évènement")
                                ->relationship('event', 'title')
                                ->placeholder("Nom de l'évènement")
                                ->columnSpanFull()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('title')
                                ->label('Titre de civilité')
                                ->options([
                                    'Mr' => 'Monsieur',
                                    'Mms' => 'Madame',
                                    'Mlle' => 'Mademoiselle',
                                ])
                                ->reactive()
                                ->preload()
                                ->searchable()
                                ->native(false)
                                ->columnSpanFull()
                                ->required(),
                        ]),
                    Forms\Components\Section::make('Autre informations')
                        ->columns(2)
                        ->schema([
                            Forms\Components\TextInput::make('position')
                                ->label('Votre poste')
                                ->placeholder('Poste occuper')
                                ->columnSpanFull()
                                ->required(),
                            Forms\Components\TextInput::make('reference')
//                                ->default(Str::random(16))
//                                ->disabled()
//                                ->dehydrated()
                                ->required()
                                ->placeholder('Code reservation')
                                ->maxLength(16)
                                ->label('Référence de la reservation')
                                ->unique(Booking::class, 'reference', ignoreRecord: true)
                                ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('reference')
                    ->toggleable()
                    ->copyable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('event.title')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->toggleable()
                    ->sortable()
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event_id')
                    ->name('event_id')
                    ->label('Événement')
                    ->options(
                        Event::all()->pluck('title', 'id')->toArray()
                    )
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ViewAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('Now, now, don\'t be cheeky, leave some records for others to play with!')
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
            'view' => Pages\ViewBooking::route('/{record}/view'),
        ];
    }
}
