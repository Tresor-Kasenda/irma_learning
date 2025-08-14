<?php

namespace App\Filament\Resources;

use App\Enums\CertificateStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Certifications';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du Certificat')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name', modifyQueryUsing: function ($query) {
                                $query->where('role', UserRoleEnum::STUDENT);
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\Select::make('formation_id')
                            ->relationship('formation', 'title', modifyQueryUsing: function ($query) {
                                return $query->active();
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('certificate_number')
                            ->label('Numéro de certificat')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->options(CertificateStatusEnum::class)
                            ->default(CertificateStatusEnum::ACTIVE)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Détails de Performance')
                    ->schema([
                        Forms\Components\TextInput::make('final_score')
                            ->label('Score final')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('issue_date')
                            ->label('Date d\'émission')
                            ->default(now())
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('expiry_date')
                            ->label('Date d\'expiration')
                            ->native(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Métadonnées')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Métadonnées additionnelles')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('certificate_number')
                    ->label('Numéro')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('formation.title')
                    ->label('Formation')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('final_score')
                    ->label('Score')
                    ->suffix('%')
                    ->color(fn($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger'))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'revoked',
                        'warning' => 'suspended',
                    ]),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Date d\'émission')
                    ->date()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_valid')
                    ->label('Valide')
                    ->boolean()
                    ->getStateUsing(fn(Certificate $record) => $record->isValid())
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('Expire le')
                    ->date()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(CertificateStatusEnum::class),

                Tables\Filters\Filter::make('valid')
                    ->label('Certificats valides')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'active')
                        ->where(function ($q) {
                            $q->whereNull('expiry_date')
                                ->orWhere('expiry_date', '>', now());
                        })),

                Tables\Filters\Filter::make('expired')
                    ->label('Certificats expirés')
                    ->query(fn(Builder $query): Builder => $query->where('expiry_date', '<', now())),

                Tables\Filters\Filter::make('high_score')
                    ->label('Score élevé (≥80%)')
                    ->query(fn(Builder $query): Builder => $query->where('final_score', '>=', 80)),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn(Certificate $record) => $record->download_url)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('verify')
                    ->label('Vérifier')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->url(fn(Certificate $record) => $record->verification_url)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('revoke')
                    ->label('Révoquer')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn(Certificate $record) => $record->update(['status' => CertificateStatusEnum::REVOKED]))
                    ->visible(fn(Certificate $record) => $record->status === CertificateStatusEnum::ACTIVE),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulk_revoke')
                        ->label('Révoquer sélectionnés')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->update(['status' => CertificateStatusEnum::REVOKED])),
                ]),
            ])
            ->defaultSort('issue_date', 'desc');
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
            'index' => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'view' => Pages\ViewCertificate::route('/{record}'),
            'edit' => Pages\EditCertificate::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }
}
