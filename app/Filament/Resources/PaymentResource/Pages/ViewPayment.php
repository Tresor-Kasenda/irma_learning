<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informations du paiement')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID du paiement'),
                        TextEntry::make('user.name')
                            ->label('Utilisateur'),
                        TextEntry::make('user.email')
                            ->label('Email utilisateur')
                            ->copyable(),
                        TextEntry::make('formation.title')
                            ->label('Formation'),
                        TextEntry::make('amount')
                            ->label('Montant')
                            ->money('EUR'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),
                    ])
                    ->columns(3),

                Section::make('Détails de transaction')
                    ->schema([
                        TextEntry::make('transaction_id')
                            ->label('ID de transaction')
                            ->copyable(),
                        TextEntry::make('payment_method')
                            ->label('Méthode de paiement'),
                        TextEntry::make('gateway')
                            ->label('Passerelle'),
                        TextEntry::make('invoice_number')
                            ->label('Numéro de facture')
                            ->getStateUsing(fn(Payment $record): string => $record->generateInvoiceNumber()),
                    ])
                    ->columns(2),

                Section::make('Dates importantes')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime(),
                        TextEntry::make('processed_at')
                            ->label('Traité le')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Réponse de la passerelle')
                    ->schema([
                        KeyValueEntry::make('gateway_response')
                            ->label('Données de réponse')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes administratives')
                            ->columnSpanFull()
                            ->placeholder('Aucune note'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
