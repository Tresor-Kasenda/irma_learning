<?php

namespace App\Filament\Resources;

use App\Enums\PaymentStatusEnum;
use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Paiements';

    protected static ?string $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('formation.title')
                    ->label('Formation')
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                        'refunded' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Méthode')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('gateway')
                    ->label('Passerelle')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processed_at')
                    ->label('Traité le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('formation')
                    ->label('Formation')
                    ->relationship('formation', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(PaymentStatusEnum::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Méthode de paiement')
                    ->options([
                        'credit_card' => 'Carte de crédit',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'bank_transfer' => 'Virement bancaire',
                    ]),

                Tables\Filters\Filter::make('amount_range')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->label('Montant minimum')
                            ->numeric()
                            ->prefix('€'),
                        Forms\Components\TextInput::make('amount_to')
                            ->label('Montant maximum')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['amount_from'], fn(Builder $query, $amount): Builder => $query->where('amount', '>=', $amount))
                            ->when($data['amount_to'], fn(Builder $query, $amount): Builder => $query->where('amount', '<=', $amount));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_success')
                    ->label('Marquer comme réussi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('Cette action va valider le paiement et activer l\'inscription à la formation.')
                    ->action(function (Payment $record) {
                        $record->markAsSuccess();

                        Notification::make()
                            ->title('Paiement validé')
                            ->body('Le paiement a été marqué comme réussi et l\'inscription est maintenant active.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Payment $record): bool => $record->status !== 'success'),

                Tables\Actions\Action::make('generate_invoice')
                    ->label('Générer facture')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn(Payment $record): string => route('payments.invoice', $record))
                    ->openUrlInNewTab()
                    ->visible(fn(Payment $record): bool => $record->status === 'success'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du paiement')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Utilisateur')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('formation_id')
                            ->label('Formation')
                            ->relationship('formation', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Montant')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(PaymentStatusEnum::class)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Détails de transaction')
                    ->schema([
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('ID de transaction')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('payment_method')
                            ->label('Méthode de paiement')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('gateway')
                            ->label('Passerelle de paiement')
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('processed_at')
                            ->label('Traité le')
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Réponse de la passerelle')
                    ->schema([
                        Forms\Components\KeyValue::make('gateway_response')
                            ->label('Données de réponse')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes administratives')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['user', 'formation']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
