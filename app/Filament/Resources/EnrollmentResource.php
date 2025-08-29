<?php

namespace App\Filament\Resources;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Formation';
    protected static ?string $navigationLabel = 'Inscriptions & Paiements';
    protected static ?int $navigationSort = 2;

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
                    ->label('Statut Inscription')
                    ->formatStateUsing(fn($state) => $state->getLabel())
                    ->color(fn(EnrollmentStatusEnum $state): string => match ($state) {
                        EnrollmentStatusEnum::Suspended => 'warning',
                        EnrollmentStatusEnum::Active => 'primary',
                        EnrollmentStatusEnum::Completed => 'success',
                        EnrollmentStatusEnum::Cancelled => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Statut Paiement')
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

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Méthode')
                    ->badge()
                    ->toggleable(),

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

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Méthode de paiement')
                    ->options([
                        'credit_card' => 'Carte de crédit',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'bank_transfer' => 'Virement bancaire',
                        'cash' => 'Espèces',
                        'cheque' => 'Chèque',
                    ]),

                Tables\Filters\Filter::make('completed')
                    ->label('Formations complétées')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'completed')),

                Tables\Filters\Filter::make('active_paid')
                    ->label('Inscriptions actives et payées')
                    ->query(fn(Builder $query): Builder => $query->where('status', 'active')->where('payment_status', 'paid')),

                Tables\Filters\Filter::make('pending_payment')
                    ->label('Paiements en attente')
                    ->query(fn(Builder $query): Builder => $query->where('payment_status', 'pending')),
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
                        ->modalDescription('Cette action va marquer le paiement comme réussi et activer l\'inscription.')
                        ->action(function (Enrollment $record) {
                            $record->update([
                                'payment_status' => EnrollmentPaymentEnum::PAID,
                                'payment_processed_at' => now(),
                                'status' => EnrollmentStatusEnum::Active,
                            ]);

                            Notification::make()
                                ->title('Paiement validé')
                                ->body('Le paiement a été marqué comme réussi et l\'inscription est maintenant active.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn(Enrollment $record) => $record->payment_status !== EnrollmentPaymentEnum::PAID),

                    Tables\Actions\Action::make('generateInvoice')
                        ->label('Générer facture')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(fn(Enrollment $record): string => route('enrollments.invoice', $record))
                        ->openUrlInNewTab()
                        ->visible(fn(Enrollment $record): bool => $record->payment_status === EnrollmentPaymentEnum::PAID),

                    Tables\Actions\Action::make('refund')
                        ->label('Rembourser')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalDescription('Cette action va marquer le paiement comme remboursé et suspendre l\'inscription.')
                        ->form([
                            Forms\Components\Textarea::make('refund_reason')
                                ->label('Raison du remboursement')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (Enrollment $record, array $data) {
                            $record->update([
                                'payment_status' => EnrollmentPaymentEnum::REFUNDED,
                                'status' => EnrollmentStatusEnum::Suspended,
                                'payment_notes' => ($record->payment_notes ? $record->payment_notes . "\n\n" : '') .
                                    'REMBOURSEMENT: ' . $data['refund_reason'] . ' (' . now()->format('d/m/Y H:i') . ')',
                            ]);

                            Notification::make()
                                ->title('Remboursement effectué')
                                ->body('Le paiement a été marqué comme remboursé.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn(Enrollment $record): bool => $record->payment_status === EnrollmentPaymentEnum::PAID),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulk_mark_paid')
                        ->label('Marquer comme payés')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'payment_status' => EnrollmentPaymentEnum::PAID,
                                    'payment_processed_at' => now(),
                                    'status' => EnrollmentStatusEnum::Active,
                                ]);
                            }

                            Notification::make()
                                ->title('Paiements validés')
                                ->body(count($records) . ' paiements ont été marqués comme réussis.')
                                ->success()
                                ->send();
                        }),

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations d\'Inscription')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name', function ($query) {
                                $query->where('role', UserRoleEnum::STUDENT)
                                    ->whereDoesntHave('enrollments', function ($subQuery) {
                                        $subQuery->where('formation_id', request('formation_id'))
                                            ->where('payment_status', EnrollmentPaymentEnum::PAID);
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

                Forms\Components\Section::make('Gestion Complète du Paiement')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('payment_status')
                                    ->label('Statut de paiement')
                                    ->options(EnrollmentPaymentEnum::class)
                                    ->default(EnrollmentPaymentEnum::PENDING)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state === EnrollmentPaymentEnum::PAID) {
                                            $set('payment_processed_at', now());
                                        }
                                    }),

                                Forms\Components\TextInput::make('amount_paid')
                                    ->label('Montant payé')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->required(),
                            ]),

                        Forms\Components\Fieldset::make('Détails de Transaction')
                            ->schema([
                                Forms\Components\TextInput::make('payment_transaction_id')
                                    ->label('ID de transaction')
                                    ->maxLength(255)
                                    ->placeholder('Ex: pi_1234567890')
                                    ->disabled(),

                                Forms\Components\Select::make('payment_method')
                                    ->label('Méthode de paiement')
                                    ->native(false)
                                    ->options([
                                        'credit_card' => 'Carte de crédit',
                                        'paypal' => 'PayPal',
                                        'stripe' => 'Stripe',
                                        'bank_transfer' => 'Virement bancaire',
                                        'cash' => 'Espèces',
                                        'cheque' => 'Chèque',
                                    ])
                                    ->placeholder('Sélectionnez une méthode'),

                                Forms\Components\Select::make('payment_gateway')
                                    ->label('Passerelle de paiement')
                                    ->options([
                                        'stripe' => 'Stripe',
                                        'paypal' => 'PayPal',
                                        'square' => 'Square',
                                        'manual' => 'Manuel',
                                    ])
                                    ->placeholder('Sélectionnez une passerelle'),

                                Forms\Components\DateTimePicker::make('payment_processed_at')
                                    ->label('Traité le')
                                    ->native(false)
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(2),

                        Forms\Components\KeyValue::make('payment_gateway_response')
                            ->label('Réponse de la passerelle')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Notes sur le paiement')
                            ->rows(3)
                            ->placeholder('Notes administratives sur la transaction...')
                            ->columnSpanFull(),
                    ]),
            ]);
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
        return static::getModel()::where('payment_status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'formation']);
    }
}
