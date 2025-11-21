<?php

namespace App\Filament\Resources;

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom complet')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('username')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255),


                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'email', ignoreRecord: true),

                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Compte et sécurité')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->revealable()
                            ->live()
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation, Get $get): bool => $operation === 'create')
                            ->label('Mot de passe'),

                        TextInput::make('password_confirmation')
                            ->password()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->same('password')
                            ->dehydrated(false)
                            ->visible(fn(Get $get) => $get('password'))
                            ->label('Confirmer le mot de passe'),

                        Forms\Components\Toggle::make('must_change_password')
                            ->columnSpanFull()
                            ->label('Forcer le changement de mot de passe')
                            ->helperText('L\'utilisateur devra changer son mot de passe à la prochaine connexion'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Rôle et statut')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Rôle')
                            ->options(
                                collect(UserRoleEnum::cases())
                                    ->take(7)
                                    ->mapWithKeys(fn($role) => [$role->value => $role->getLabel()])
                            )
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(
                                collect(UserStatusEnum::cases())
                                    ->take(7)
                                    ->mapWithKeys(fn($role) => [$role->value => $role->getLabel()])
                            )
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn(UserRoleEnum $state) => $state->getLabel())
                    ->color(fn(UserRoleEnum $state): string => match ($state) {
                        UserRoleEnum::ADMIN => 'danger',
                        UserRoleEnum::INSTRUCTOR => 'warning',
                        UserRoleEnum::STUDENT => 'success',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn(UserStatusEnum $state) => $state->getLabel())
                    ->color(fn(UserStatusEnum $state): string => match ($state) {
                        UserStatusEnum::ACTIVE => 'success',
                        UserStatusEnum::INACTIVE => 'warning',
                        UserStatusEnum::BANNED => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options(UserRoleEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(UserStatusEnum::class)
                    ->multiple(),

                Tables\Filters\Filter::make('email_verified')
                    ->label('Email vérifié')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('email_verified_at')),

                Tables\Filters\Filter::make('must_change_password')
                    ->label('Doit changer le mot de passe')
                    ->query(fn(Builder $query): Builder => $query->where('must_change_password', true)),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Voir')
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->label('Modifier')
                        ->icon('heroicon-o-pencil')
                        ->color('info'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer')
                        ->icon('heroicon-o-trash')
                        ->visible(fn(User $record) => $record->role->value !== UserRoleEnum::ADMIN->value)
                        ->color('danger')
                        ->requiresConfirmation(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggle_status')
                        ->label('Activer/Désactiver')
                        ->icon('heroicon-o-power')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $newStatus = $record->status === 'active' ? 'inactive' : 'active';
                                $record->update(['status' => $newStatus]);
                            }
                        })
                ]),

            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FormationsRelationManager::class,
            RelationManagers\ExamAttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
