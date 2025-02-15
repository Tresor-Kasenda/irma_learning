<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EventTypeResource\Pages;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class EventTypeResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Gestions des events'; // Groupe de menu

    protected static ?int $navigationSort = 0;

    public static function getLabel(): ?string
    {
        return "Type d'événement";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make("Type d'evenement")
                    ->schema([
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes())
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('title')
                    ->sortable()
                    ->label("Nom de l'evenement")
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->successNotification(function ($notification) {
                        return $notification
                            ->title('Type d\'evenement supprimé');
                    }),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventTypes::route('/'),
            'create' => Pages\CreateEventType::route('/create'),
            'edit' => Pages\EditEventType::route('/{record}/edit'),
        ];
    }
}
