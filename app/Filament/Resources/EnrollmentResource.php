<?php

namespace App\Filament\Resources;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use IbrahimBougaoua\FilaProgress\Infolists\Components\ProgressBarEntry;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Formation';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations d\'Inscription')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\Select::make('formation_id')
                            ->relationship('formation', 'title')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('enrollment_date')
                            ->label('Date d\'inscription')
                            ->default(now())
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\Select::make('status')
                            ->options(EnrollmentStatusEnum::class)
                            ->default(EnrollmentStatusEnum::Active)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informations de Paiement')
                    ->schema([
                        Forms\Components\Select::make('payment_status')
                            ->label('Statut de paiement')
                            ->options(EnrollmentPaymentEnum::class)
                            ->default(EnrollmentPaymentEnum::PENDING)
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Montant payé')
                            ->numeric()
                            ->prefix('€')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('payment_method')
                            ->label('Méthode de paiement')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('transaction_id')
                            ->label('ID de transaction')
                            ->maxLength(255)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Progression')
                    ->schema([
                        Forms\Components\TextInput::make('progress_percentage')
                            ->label('Pourcentage de progression')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->disabled()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('completion_date')
                            ->label('Date de completion')
                            ->native(false)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('last_accessed_at')
                            ->label('Dernier accès')
                            ->native(false)
                            ->disabled()
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Actions')
                    ->schema([
                        Forms\Components\Actions::make([
                            Action::make('updateProgress')
                                ->label('Mettre à jour la progression')
                                ->icon('heroicon-o-arrow-path')
                                ->action(function (Enrollment $record) {
                                    $record->updateProgress();
                                })
                                ->visible(fn($context) => $context === 'edit'),
                        ]),
                    ])
                    ->visible(fn($context) => $context === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('formation.title')
                    ->label('Formation')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'active',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'secondary' => 'suspended',
                    ]),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Paiement')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ]),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progression')
                    ->getStateUsing(fn($record) => $record->progress_percentage ?? 0),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('Inscrit le')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_accessed_at')
                    ->label('Dernier accès')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(EnrollmentStatusEnum::class),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Statut de paiement')
                    ->options(EnrollmentPaymentEnum::class),

                Tables\Filters\Filter::make('completed')
                    ->label('Formations complétées')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'completed')),

                Tables\Filters\Filter::make('active_paid')
                    ->label('Inscriptions actives et payées')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'active')->where('payment_status', 'paid')),

                Tables\Filters\Filter::make('high_progress')
                    ->label('Progression élevée (≥75%)')
                    ->query(fn(Builder $query): Builder => $query->where('progress_percentage', '>=', 75)),
            ])
            ->actions([
                Tables\Actions\Action::make('updateProgress')
                    ->label('Mettre à jour')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(fn(Enrollment $record) => $record->updateProgress())
                    ->successNotificationTitle('Progression mise à jour'),

                Tables\Actions\Action::make('markPaid')
                    ->label('Marquer payé')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(Enrollment $record) => $record->update(['payment_status' => EnrollmentPaymentEnum::PAID]))
                    ->visible(fn(Enrollment $record) => $record->payment_status !== EnrollmentPaymentEnum::PAID),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulk_update_progress')
                        ->label('Mettre à jour la progression')
                        ->icon('heroicon-o-arrow-path')
                        ->color('primary')
                        ->action(fn($records) => $records->each->updateProgress())
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('enrollment_date', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations de l\'Inscription')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Utilisateur'),
                        TextEntry::make('user.email')
                            ->label('Email'),
                        TextEntry::make('formation.title')
                            ->label('Formation'),
                        TextEntry::make('formation.duration')
                            ->label('Durée')
                            ->suffix(' heures'),
                    ])
                    ->columns(2),

                Section::make('Statut et Paiement')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'active' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'suspended' => 'secondary',
                            }),
                        TextEntry::make('payment_status')
                            ->label('Statut de paiement')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'paid' => 'success',
                                'failed' => 'danger',
                                'refunded' => 'secondary',
                            }),
                        TextEntry::make('amount_paid')
                            ->label('Montant payé')
                            ->money('EUR'),
                        TextEntry::make('enrollment_date')
                            ->label('Date d\'inscription')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Progression')
                    ->schema([
                        ProgressBarEntry::make('progress_percentage')
                            ->label('Progression')
                            ->color('primary')
                            ->getStateUsing(fn($record) => $record->progress_percentage ?? 0),
                        TextEntry::make('completion_date')
                            ->label('Date de completion')
                            ->dateTime()
                            ->placeholder('Non complétée'),
                        TextEntry::make('last_accessed_at')
                            ->label('Dernier accès')
                            ->dateTime()
                            ->since(),
                    ])
                    ->columns(2),

                Section::make('Détails du Paiement')
                    ->schema([
                        TextEntry::make('payment_method')
                            ->label('Méthode de paiement'),
                        TextEntry::make('transaction_id')
                            ->label('ID de transaction')
                            ->copyable(),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record->payment_status === EnrollmentPaymentEnum::PAID),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //UserProgressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'view' => Pages\ViewEnrollment::route('/{record}'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'formation']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'user.email', 'formation.title'];
    }
}
