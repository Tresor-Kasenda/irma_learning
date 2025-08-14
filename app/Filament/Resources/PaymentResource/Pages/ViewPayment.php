<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPayment extends ViewRecord
{
    protected static string $resource = PaymentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations du paiement')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID du paiement'),
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Utilisateur'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email utilisateur')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('formation.title')
                            ->label('Formation'),
                        Infolists\Components\TextEntry::make('amount')
                            ->label('Montant')
                            ->money('EUR'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Détails de transaction')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaction_id')
                            ->label('ID de transaction')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Méthode de paiement'),
                        Infolists\Components\TextEntry::make('gateway')
                            ->label('Passerelle'),
                        Infolists\Components\TextEntry::make('invoice_number')
                            ->label('Numéro de facture')
                            ->getStateUsing(fn(Payment $record): string => $record->generateInvoiceNumber()),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Dates importantes')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('processed_at')
                            ->label('Traité le')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Réponse de la passerelle')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('gateway_response')
                            ->label('Données de réponse')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
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
