<?php

namespace App\Filament\Resources;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Formation';

    protected static ?string $navigationLabel = 'Inscriptions';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations d\'Inscription')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name', function ($query) {
                                $query->where('role', '=', UserRoleEnum::STUDENT)
                                    ->whereDoesntHave('enrollments', function ($query) {
                                        $query->where('status', '!=', EnrollmentStatusEnum::Cancelled);
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('formation_id')
                            ->relationship('formation', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('enrollment_date')
                            ->label('Date d\'inscription')
                            ->default(now())
                            ->native(false)
                            ->required(),

                        Forms\Components\DateTimePicker::make('completion_date')
                            ->label('Date de completion')
                            ->native(false)
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->options(EnrollmentStatusEnum::class)
                            ->default(EnrollmentStatusEnum::Active)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informations de Paiement')
                    ->schema([
                        Forms\Components\Select::make('payment_status')
                            ->label('Statut de paiement')
                            ->options(EnrollmentPaymentEnum::class)
                            ->default(EnrollmentPaymentEnum::PENDING)
                            ->required(),

                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Montant payé')
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->required(),
                    ])
                    ->columns(2),
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn($state) => $state->getLabel())
                    ->color(fn(EnrollmentStatusEnum $state): string => match ($state) {
                        EnrollmentStatusEnum::Suspended => 'warning',
                        EnrollmentStatusEnum::Active => 'primary',
                        EnrollmentStatusEnum::Completed => 'success',
                        EnrollmentStatusEnum::Cancelled => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->formatStateUsing(fn($state) => $state->getLabel())
                    ->color(fn(EnrollmentPaymentEnum $state): string => match ($state) {
                        EnrollmentPaymentEnum::PENDING => 'warning',
                        EnrollmentPaymentEnum::PAID => 'success',
                        EnrollmentPaymentEnum::FAILED => 'danger',
                        EnrollmentPaymentEnum::REFUNDED => 'secondary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('Inscrit le')
                    ->date()
                    ->sortable(),
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Voir')
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->label('Modifier')
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\Action::make('markPaid')
                        ->label('Marquer payé')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn(Enrollment $record) => $record->update(['payment_status' => EnrollmentPaymentEnum::PAID]))
                        ->visible(fn(Enrollment $record) => $record->payment_status !== EnrollmentPaymentEnum::PAID),
                ])
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

    public static function getRelations(): array
    {
        return [];
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
}
