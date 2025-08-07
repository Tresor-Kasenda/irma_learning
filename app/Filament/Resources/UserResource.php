<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Gestion des inscriptions';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $label = 'Utilisateurs';

    protected static ?int $navigationSort = 3;

    public static function getLabel(): ?string
    {
        return 'Utilisateurs';
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
                            ->required(),
                        Forms\Components\TextInput::make('username')
                            ->label('Post Nom')
                            ->placeholder('votre post nom')
                            ->required(),
                        Forms\Components\TextInput::make('firstname')
                            ->label('Prénom')
                            ->placeholder('votre prenom')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->placeholder('votre email')
                            ->email()
                            ->required(),
                        Forms\Components\Select::make('role')
                            ->label('Rôle')
                            ->options([
                                UserRoleEnum::ADMIN->value => 'ADMIN',
                                UserRoleEnum::SUPPORT->value => 'SUPPORT',
                                UserRoleEnum::MANAGER->value => 'MANAGER',
                            ])
                            ->searchable(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->placeholder('+243xxxxxxxxx')
                            ->tel()
                            ->regex('/^\+[1-9]\d{1,14}$/')
                            ->helperText('Le numéro doit commencer par l\'indicatif du pays (ex: +243)')
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->revealable()
                            ->live()
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation, Get $get): bool => $operation === 'create')
                            ->label('Mot de passe'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->same('password')
                            ->dehydrated(false)
                            ->visible(fn(Get $get) => $get('password'))
                            ->label('Confirmer le mot de passe'),
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Photo de profile')
                            ->image()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->imageEditor()
                            ->circleCropper()
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('must_change_password')
                            ->label('Forcer le changement de mot de passe')
                            ->helperText('Si activé, l\'utilisateur devra changer son mot de passe à sa prochaine connexion')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        UserRoleEnum::ADMIN->value => 'danger',
                        UserRoleEnum::STUDENT->value => 'success',
                        default => 'warning',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference_code')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Filtrer par rôle')
                    ->options([
                        UserRoleEnum::STUDENT->value => 'Étudiants',
                        UserRoleEnum::ADMIN->value => 'Administrateurs',
                        UserRoleEnum::SUPPORT->value => 'Support',
                        UserRoleEnum::MANAGER->value => 'Manager',
                    ])
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn(User $record): bool => static::canEdit($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn(User $record): bool => static::canDelete($record)),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->heading('Tous les participants');
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->role === UserRoleEnum::ROOT->value;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->role === UserRoleEnum::ROOT->value;
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->check(); // All authenticated users can view
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        return in_array($user->role, [
            UserRoleEnum::ROOT->value,
            UserRoleEnum::ADMIN->value
        ]);
    }
}
