<?php

namespace App\Filament\Resources;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Gestion des inscriptions';


    protected static ?string $label = 'Gestion des résultats'; // Nom de la ressource

    protected static ?int $navigationSort = 5;

    public static function getLabel(): ?string
    {
        return 'Gestion des résultats';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->placeholder('votre nom')
                            ->readOnly()
                            ->required(),
                        Forms\Components\TextInput::make('username')
                            ->label('Post Nom')
                            ->placeholder('votre post nom')
                            ->readOnly()
                            ->required(),
                        Forms\Components\TextInput::make('firstname')
                            ->readOnly()
                            ->label('Prénom')
                            ->placeholder('votre prenom')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->placeholder('votre email')
                            ->readOnly()
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->placeholder('+243xxxxxxxxx')
                            ->tel()
                            ->readOnly()
                            ->regex('/^\+[1-9]\d{1,14}$/')
                            ->helperText('Le numéro doit commencer par l\'indicatif du pays (ex: +243)')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->modifyQueryUsing(function (Builder $query) {
            $query->where('role', UserRoleEnum::STUDENT->value);
        })
            ->columns([
                Tables\Columns\TextColumn::make('reference_code')
                    ->toggleable()
                    ->copyable()
                    ->sortable()
                    ->searchable(),
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('manageStudentResults')
                    ->label('Gérer résultats')
                    ->icon('heroicon-o-academic-cap')
                    ->url(fn(User $record): string => route('filament.admin.resources.students.edit', [
                        'record' => $record,
                        'activeRelationManager' => 'results'
                    ]))
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            RelationManagers\ResultsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
