<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormationAccessCodeResource\Pages;
use App\Models\FormationAccessCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FormationAccessCodeResource extends Resource
{
    protected static ?string $model = FormationAccessCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    protected static ?string $navigationLabel = 'Codes d\'accès';

    protected static ?string $navigationGroup = 'Formations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('formation_id')
                    ->relationship('formation', 'title')
                    ->required()
                    ->searchable(),
                    
                Forms\Components\TextInput::make('code')
                    ->default(fn() => Str::random(8))
                    ->required()
                    ->unique(ignoreRecord: true),
                    
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Date d\'expiration'),
                    
                Forms\Components\Toggle::make('is_used')
                    ->label('Utilisé')
                    ->disabled()
                    ->dehydrated(false),
                    
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Utilisé par')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('formation.title')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                    
                Tables\Columns\IconColumn::make('is_used')
                    ->boolean()
                    ->label('Utilisé'),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisé par')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('used_at')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('formation')
                    ->relationship('formation', 'title'),
                    
                Tables\Filters\TernaryFilter::make('is_used')
                    ->label('Statut d\'utilisation'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormationAccessCodes::route('/'),
            'create' => Pages\CreateFormationAccessCode::route('/create'),
            'edit' => Pages\EditFormationAccessCode::route('/{record}/edit'),
        ];
    }
}
